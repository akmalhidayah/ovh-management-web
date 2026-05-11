<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormSubmissionAttachment;
use App\Models\QcFormTemplate;
use App\Support\QcTemplates\FixedQcTemplate;
use App\Support\UserRoleUiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class FormController extends Controller
{
    private const ERROR_STORE = 'QC-SUB-STORE-FAILED';
    private const ERROR_UPDATE = 'QC-SUB-UPDATE-FAILED';
    private const ERROR_PDF = 'QC-SUB-PDF-FAILED';
    private const ERROR_DESTROY = 'QC-SUB-DESTROY-FAILED';
    private const ALLOWED_ATTACHMENT_MIMES = 'jpg,jpeg,png';

    public function create(Request $request): View
    {
        $templates = QcFormTemplate::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedTemplate = null;

        if ($templates->isNotEmpty()) {
            $requestedTemplate = $request->input('template', $request->old('template_id'));

            $selectedTemplate = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
                ->where('status', 'active')
                ->when($requestedTemplate, fn ($query) => $query->whereKey($requestedTemplate))
                ->first();

            if (! $selectedTemplate) {
                $selectedTemplate = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->first();
            }
        }

        return view('user.qc.forms.create', array_merge(UserRoleUiData::qcForm(), [
            'templates' => $templates,
            'selectedTemplate' => $selectedTemplate,
            'autoDocNumber' => $this->generateQcDocumentNumber(),
            'activeQcMasterDataRecords' => $this->activeQcMasterDataRecords(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubmissionRequest($request);

        $template = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
            ->where('status', 'active')
            ->findOrFail($validated['template_id']);

        if ($template->template_type && $validated['action'] === 'submit') {
            $this->validateFixedSubmission($request, $template);
        }

        try {
            $submission = DB::transaction(function () use ($request, $template, $validated) {
                $isFixedTemplate = (bool) $template->template_type;
                $generalInfo = $isFixedTemplate ? $this->fixedHeaderData($request, null, true) : $request->input('general_info', []);
                $bodyData = $isFixedTemplate ? $this->fixedBodyData($request, $template) : null;
                $formNumber = $generalInfo['doc_number'] ?? $generalInfo['report_no'] ?? null;
                $formNumber = $formNumber ?: $this->generateFormNumber();
                $status = $validated['action'] === 'submit' ? 'submitted' : 'draft';
                $templateMeta = collect($template->blocks)->pluck('config')->pluck('meta')->filter()->first() ?? [];
                $dateTime = $generalInfo['date_time'] ?? null;

                $submission = QcFormSubmission::create([
                    'qc_form_template_id' => $template->id,
                    'user_id' => $request->user()?->id,
                    'form_number' => $formNumber,
                    'status' => $status,
                    'submitted_at' => $status === 'submitted' ? now() : null,
                    'year' => $generalInfo['tahun'] ?? null,
                    'plant' => $generalInfo['plant'] ?? $generalInfo['ovh_plant'] ?? null,
                    'area' => $generalInfo['area'] ?? null,
                    'equipment' => $generalInfo['name_equipment'] ?? $generalInfo['alat'] ?? $generalInfo['equipment'] ?? ($templateMeta['equipment'] ?? null),
                    'report_no' => $generalInfo['doc_number'] ?? $generalInfo['report_no'] ?? $formNumber,
                    'ovh_plant' => $generalInfo['ovh_plant'] ?? null,
                    'unit' => $generalInfo['unit_kerja'] ?? $generalInfo['unit'] ?? null,
                    'tag_num' => $generalInfo['tag_num'] ?? null,
                    'tgl_mulai' => $generalInfo['tgl_mulai'] ?? ($dateTime ? Carbon::parse($dateTime)->toDateString() : null),
                    'pekerjaan' => $generalInfo['pekerjaan'] ?? ($templateMeta['pekerjaan'] ?? null),
                    'durasi' => $generalInfo['durasi'] ?? null,
                    'general_info' => $generalInfo,
                    'body_data' => $bodyData,
                    'note' => $request->input('note'),
                    'approval_data' => $request->input('approval', []),
                ]);

                if ($isFixedTemplate) {
                    $this->storeFixedRows($submission, $template, $bodyData ?? []);
                } else {
                    $this->storeRows($submission, $template, $request->input('rows', []));
                }

                $this->storeAttachments($submission, $template, $request->file('attachments', []));

                return $submission;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_STORE, $exception, [
                'template_id' => $template->id,
                'requested_status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
            ]);

            return back()
                ->withInput()
                ->withErrors(['submission' => 'Form QC gagal disimpan. Kode error: '.self::ERROR_STORE]);
        }

        $this->logStatus('qc_submission_saved', [
            'submission_id' => $submission->id,
            'template_id' => $template->id,
            'status' => $submission->status,
        ]);

        if ($submission->status === 'submitted') {
            return redirect()
                ->route('user.qc.history.index')
                ->with('success', 'Form QC berhasil disubmit.');
        }

        return redirect()
            ->route('user.qc.drafts.index')
            ->with('success', 'Draft QC berhasil disimpan.');
    }

    public function edit(QcFormSubmission $submission): View
    {
        $this->authorizeSubmission($submission);

        abort_unless($submission->status === 'draft', 403);

        $submission->load(['template.blocks.fields', 'template.blocks.tableRows', 'template.fields', 'template.tableRows', 'rows', 'attachments']);

        $templates = QcFormTemplate::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('user.qc.forms.create', array_merge(UserRoleUiData::qcForm(), [
            'templates' => $templates,
            'selectedTemplate' => $submission->template,
            'draftSubmission' => $submission,
            'autoDocNumber' => $submission->general_info['doc_number'] ?? $submission->report_no ?? $submission->form_number,
            'activeQcMasterDataRecords' => $this->activeQcMasterDataRecords(),
        ]));
    }

    public function update(Request $request, QcFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);

        abort_unless($submission->status === 'draft', 403);

        $validated = $this->validateSubmissionRequest($request);
        abort_unless((int) $validated['template_id'] === (int) $submission->qc_form_template_id, 422);

        $template = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
            ->findOrFail($submission->qc_form_template_id);

        if ($template->template_type && $validated['action'] === 'submit') {
            $this->validateFixedSubmission($request, $template);
        }

        try {
            $submission = DB::transaction(function () use ($request, $template, $validated, $submission) {
                $isFixedTemplate = (bool) $template->template_type;
                $generalInfo = $isFixedTemplate ? $this->fixedHeaderData($request, $submission->form_number) : $request->input('general_info', []);
                $bodyData = $isFixedTemplate ? $this->fixedBodyData($request, $template) : null;
                $formNumber = $generalInfo['doc_number'] ?? $generalInfo['report_no'] ?? null;
                $formNumber = $formNumber ?: $submission->form_number ?: $this->generateFormNumber();
                $status = $validated['action'] === 'submit' ? 'submitted' : 'draft';
                $templateMeta = collect($template->blocks)->pluck('config')->pluck('meta')->filter()->first() ?? [];
                $dateTime = $generalInfo['date_time'] ?? null;

                $submission->update([
                    'form_number' => $formNumber,
                    'status' => $status,
                    'submitted_at' => $status === 'submitted' ? now() : null,
                    'year' => $generalInfo['tahun'] ?? null,
                    'plant' => $generalInfo['plant'] ?? $generalInfo['ovh_plant'] ?? null,
                    'area' => $generalInfo['area'] ?? null,
                    'equipment' => $generalInfo['name_equipment'] ?? $generalInfo['alat'] ?? $generalInfo['equipment'] ?? ($templateMeta['equipment'] ?? null),
                    'report_no' => $generalInfo['doc_number'] ?? $generalInfo['report_no'] ?? $formNumber,
                    'ovh_plant' => $generalInfo['ovh_plant'] ?? null,
                    'unit' => $generalInfo['unit_kerja'] ?? $generalInfo['unit'] ?? null,
                    'tag_num' => $generalInfo['tag_num'] ?? null,
                    'tgl_mulai' => $generalInfo['tgl_mulai'] ?? ($dateTime ? Carbon::parse($dateTime)->toDateString() : null),
                    'pekerjaan' => $generalInfo['pekerjaan'] ?? ($templateMeta['pekerjaan'] ?? null),
                    'durasi' => $generalInfo['durasi'] ?? null,
                    'general_info' => $generalInfo,
                    'body_data' => $bodyData,
                    'note' => $request->input('note'),
                    'approval_data' => $request->input('approval', []),
                ]);

                $submission->rows()->delete();

                if ($isFixedTemplate) {
                    $this->storeFixedRows($submission, $template, $bodyData ?? []);
                } else {
                    $this->storeRows($submission, $template, $request->input('rows', []));
                }

                $this->storeAttachments($submission, $template, $request->file('attachments', []));

                return $submission;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, [
                'submission_id' => $submission->id,
                'template_id' => $template->id,
                'requested_status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
            ]);

            return back()
                ->withInput()
                ->withErrors(['submission' => 'Draft QC gagal diperbarui. Kode error: '.self::ERROR_UPDATE]);
        }

        $this->logStatus('qc_submission_updated', [
            'submission_id' => $submission->id,
            'template_id' => $template->id,
            'status' => $submission->status,
        ]);

        if ($submission->status === 'submitted') {
            return redirect()
                ->route('user.qc.history.index')
                ->with('success', 'Form QC berhasil disubmit.');
        }

        return redirect()
            ->route('user.qc.drafts.index')
            ->with('success', 'Draft QC berhasil diperbarui.');
    }

    public function show(QcFormSubmission $submission): View
    {
        $this->authorizeSubmission($submission);
        $submission->load(['template.blocks', 'rows', 'attachments', 'user']);

        return view('user.qc.submissions.show', array_merge(UserRoleUiData::qcForm(), [
            'submission' => $submission,
            'statusLabels' => $this->statusLabels(),
        ]));
    }

    public function pdf(QcFormSubmission $submission)
    {
        $this->authorizeSubmission($submission);
        $submission->load(['template.blocks', 'rows', 'attachments', 'user']);

        try {
            $response = $this->streamPdf($submission);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_PDF, $exception, ['submission_id' => $submission->id]);

            return back()->withErrors(['pdf' => 'PDF QC gagal dibuka. Kode error: '.self::ERROR_PDF]);
        }

        $this->logStatus('qc_submission_pdf_opened', [
            'submission_id' => $submission->id,
            'status' => $submission->status,
        ]);

        return $response;
    }

    public function attachment(QcFormSubmissionAttachment $attachment)
    {
        $attachment->loadMissing('submission');
        $this->authorizeSubmission($attachment->submission);

        $path = $this->attachmentStoragePath($attachment);
        abort_unless($path, 404);
        $filename = str_replace(['"', "\r", "\n"], '', $attachment->original_name ?: 'attachment');

        return response()->file($path, [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(QcFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);

        abort_unless($submission->status === 'draft', 403);

        $submissionId = $submission->id;

        try {
            $submission->delete();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DESTROY, $exception, ['submission_id' => $submissionId]);

            return back()->withErrors(['submission' => 'Draft QC gagal dihapus. Kode error: '.self::ERROR_DESTROY]);
        }

        $this->logStatus('qc_submission_deleted', [
            'submission_id' => $submissionId,
            'status' => 'deleted',
        ]);

        return redirect()
            ->route('user.qc.drafts.index')
            ->with('success', 'Draft QC berhasil dihapus.');
    }

    public static function streamPdf(QcFormSubmission $submission)
    {
        $pdf = Pdf::loadView('pdf.qc-submission', [
            'submission' => $submission,
            'statusLabels' => self::statusLabels(),
        ])->setPaper('a4', 'portrait');

        $pekerjaan = $submission->pekerjaan ?: ($submission->general_info['pekerjaan'] ?? 'Form QC');
        $safePekerjaan = trim(preg_replace('/[\\\\\/:*?"<>|]+/', '-', (string) $pekerjaan));
        $filename = 'Quality Control - '.($safePekerjaan ?: 'Form QC').'.pdf';

        return $pdf->stream($filename);
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
        ];
    }

    private function validateSubmissionRequest(Request $request): array
    {
        return $request->validate([
            'template_id' => ['required', 'exists:qc_form_templates,id'],
            'action' => ['required', 'in:draft,submit'],
            'general_info' => ['nullable', 'array'],
            'header' => ['nullable', 'array'],
            'header.master_data_record_id' => ['nullable', 'integer'],
            'body' => ['nullable', 'array'],
            'rows' => ['nullable', 'array'],
            'note' => ['nullable', 'string'],
            'approval' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['file', 'mimes:'.self::ALLOWED_ATTACHMENT_MIMES, 'mimetypes:image/jpeg,image/png', 'max:10240'],
        ]);
    }

    private function storeRows(QcFormSubmission $submission, QcFormTemplate $template, array $blocks): void
    {
        foreach ($blocks as $blockId => $rows) {
            $block = $template->blocks->firstWhere('id', (int) $blockId);

            if (! $block) {
                continue;
            }

            foreach (array_values($rows) as $index => $row) {
                $submission->rows()->create([
                    'qc_form_template_block_id' => $block->id,
                    'block_type' => $block->type,
                    'order_no' => $index + 1,
                    'row_data' => $row,
                    'status_value' => $row['status_value'] ?? $row['status'] ?? null,
                    'catatan' => $row['catatan'] ?? $row['keterangan'] ?? null,
                    'aktual' => $row['aktual'] ?? $row['actual'] ?? null,
                ]);
            }
        }
    }

    private function fixedHeaderData(Request $request, ?string $existingDocNumber = null, bool $forceGeneratedDocNumber = false): array
    {
        $header = collect(FixedQcTemplate::headerFields())
            ->mapWithKeys(fn ($field) => [$field['key'] => $request->input("header.{$field['key']}")])
            ->all();

        $header['doc_number'] = $existingDocNumber
            ?: ($forceGeneratedDocNumber ? $this->generateQcDocumentNumber() : ($header['doc_number'] ?: $this->generateQcDocumentNumber()));

        if ($masterRecord = $this->selectedActiveQcMasterDataRecord($request)) {
            $header['master_data_record_id'] = $masterRecord->id;
            $header['functional_location'] = $masterRecord->func_location;
            $header['tahun'] = $masterRecord->year;
            $header['tag_num'] = $masterRecord->section_no;
            $header['area'] = $masterRecord->area;
            $header['id_equipment'] = $masterRecord->equipment_no;
            $header['alat'] = $masterRecord->description;
        }

        return $header;
    }

    private function fixedBodyData(Request $request, QcFormTemplate $template): array
    {
        $type = FixedQcTemplate::normalizeType($template->template_type);
        $body = $request->input('body', []);

        if ($type === FixedQcTemplate::TYPE_WELDING) {
            $schema = FixedQcTemplate::schemaForTemplate($template);
            $submittedWelderRows = collect($body['welder_rows'] ?? [])->values();
            $submittedResultRows = collect($body['result_rows'] ?? [])->values();

            return [
                'methods' => array_values($body['methods'] ?? []),
                'check_steps' => array_values($body['check_steps'] ?? []),
                'final_check' => (bool) ($body['final_check'] ?? false),
                'welder_rows' => collect($schema['welder_rows'] ?? [])
                    ->values()
                    ->map(function ($templateRow, $index) use ($submittedWelderRows) {
                        $row = $submittedWelderRows->get($index, []);

                        return [
                            'no' => (string) ($templateRow['no'] ?? $index + 1),
                            'nama_welder' => trim((string) ($row['nama_welder'] ?? $templateRow['nama_welder'] ?? '')),
                            'posisi_pengelasan' => trim((string) ($row['posisi_pengelasan'] ?? $templateRow['posisi_pengelasan'] ?? '')),
                            'diameter_electrode' => trim((string) ($row['diameter_electrode'] ?? $templateRow['diameter_electrode'] ?? '')),
                            'electrode_filter' => trim((string) ($row['electrode_filter'] ?? $templateRow['electrode_filter'] ?? '')),
                            'amper' => trim((string) ($row['amper'] ?? $templateRow['amper'] ?? '')),
                            'keterangan' => trim((string) ($row['keterangan'] ?? $templateRow['keterangan'] ?? '')),
                        ];
                    })
                    ->all(),
                'result_rows' => collect($schema['result_rows'] ?? [])
                    ->values()
                    ->map(function ($templateRow, $index) use ($submittedResultRows) {
                        $row = $submittedResultRows->get($index, []);

                        return [
                            'no' => (string) ($templateRow['no'] ?? $index + 1),
                            'deskripsi' => trim((string) ($templateRow['deskripsi'] ?? '')),
                            'status' => $row['status'] ?? null,
                            'keterangan' => trim((string) ($row['keterangan'] ?? $templateRow['keterangan'] ?? '')),
                        ];
                    })
                    ->all(),
            ];
        }

        return [
            'final_check' => (bool) ($body['final_check'] ?? false),
            'general_rows' => collect($body['general_rows'] ?? [])
                ->map(fn ($row) => [
                    'item_pengecekan' => trim((string) ($row['item_pengecekan'] ?? '')),
                    'standar' => trim((string) ($row['standar'] ?? '')),
                    'actual' => trim((string) ($row['actual'] ?? '')),
                    'status' => $row['status'] ?? null,
                    'catatan' => trim((string) ($row['catatan'] ?? '')),
                ])
                ->filter(fn ($row) => collect($row)->filter()->isNotEmpty())
                ->values()
                ->all(),
        ];
    }

    private function validateFixedSubmission(Request $request, QcFormTemplate $template): void
    {
        $errors = [];
        $headerData = $this->fixedHeaderData($request);

        foreach (FixedQcTemplate::headerFields() as $field) {
            if ($field['key'] === 'doc_number') {
                continue;
            }

            if (blank($headerData[$field['key']] ?? null)) {
                $errors["header.{$field['key']}"] = "{$field['label']} wajib diisi.";
            }
        }

        $body = $this->fixedBodyData($request, $template);
        if (! ($body['final_check'] ?? false)) {
            $errors['body.final_check'] = 'Submit QC hanya bisa dilakukan jika Final Check sudah dicentang.';
        }

        if (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_WELDING) {
            if (($body['methods'] ?? []) === []) {
                $errors['body.methods'] = 'Minimal satu metode QC wajib dipilih.';
            }

            if (($body['check_steps'] ?? []) === []) {
                $errors['body.check_steps'] = 'Minimal satu pengecekan ke wajib dipilih.';
            }

            foreach ($body['welder_rows'] ?? [] as $index => $row) {
                foreach (['nama_welder', 'posisi_pengelasan', 'diameter_electrode', 'electrode_filter', 'amper'] as $key) {
                    if (blank($row[$key] ?? null)) {
                        $errors["body.welder_rows.{$index}.{$key}"] = 'Row welder yang diisi harus lengkap.';
                    }
                }
            }

            if (($body['result_rows'] ?? []) === []) {
                $errors['body.result_rows'] = 'Minimal satu row hasil QC welding wajib diisi.';
            }

            foreach ($body['result_rows'] ?? [] as $index => $row) {
                if (blank($row['deskripsi'] ?? null)) {
                    $errors["body.result_rows.{$index}.deskripsi"] = 'Deskripsi hasil QC wajib diisi.';
                }

                if (blank($row['status'] ?? null)) {
                    $errors["body.result_rows.{$index}.status"] = 'Status hasil QC wajib dipilih.';
                } elseif (($row['status'] ?? null) !== 'Baik') {
                    $errors["body.result_rows.{$index}.status"] = 'Final Check hanya bisa dicentang jika semua hasil QC berstatus Baik.';
                }
            }
        } else {
            if (($body['general_rows'] ?? []) === []) {
                $errors['body.general_rows'] = 'Minimal satu row QC Umum wajib diisi.';
            }

            foreach ($body['general_rows'] ?? [] as $index => $row) {
                foreach (['item_pengecekan', 'standar', 'actual', 'status'] as $key) {
                    if (blank($row[$key] ?? null)) {
                        $errors["body.general_rows.{$index}.{$key}"] = 'Item pengecekan, standar, actual, dan status wajib diisi.';
                    }
                }

                if (($row['status'] ?? null) && $row['status'] !== 'Ok') {
                    $errors["body.general_rows.{$index}.status"] = 'Final Check hanya bisa dicentang jika semua status berisi Ok.';
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function storeFixedRows(QcFormSubmission $submission, QcFormTemplate $template, array $bodyData): void
    {
        if (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_WELDING) {
            foreach ($bodyData['welder_rows'] ?? [] as $index => $row) {
                $submission->rows()->create([
                    'block_type' => 'welding_welder',
                    'order_no' => $index + 1,
                    'row_data' => $row,
                    'catatan' => $row['keterangan'] ?? null,
                ]);
            }

            foreach ($bodyData['result_rows'] ?? [] as $index => $row) {
                $submission->rows()->create([
                    'block_type' => 'welding_result',
                    'order_no' => $index + 1,
                    'row_data' => $row,
                    'status_value' => $row['status'] ?? null,
                    'catatan' => $row['keterangan'] ?? null,
                ]);
            }

            return;
        }

        foreach ($bodyData['general_rows'] ?? [] as $index => $row) {
            $submission->rows()->create([
                'block_type' => 'general',
                'order_no' => $index + 1,
                'row_data' => $row,
                'status_value' => $row['status'] ?? null,
                'catatan' => $row['catatan'] ?? null,
                'aktual' => $row['actual'] ?? null,
            ]);
        }
    }

    private function storeAttachments(QcFormSubmission $submission, QcFormTemplate $template, array $attachments): void
    {
        $attachmentLabels = $template->blocks
            ->where('type', 'attachment')
            ->flatMap(fn ($block) => collect($block->config['fields'] ?? [])->mapWithKeys(fn ($field) => [
                ($field['key'] ?? $field['name'] ?? Str::snake($field['label'] ?? 'lampiran')) => $field['label'] ?? null,
            ]))
            ->merge([
                'foto_before' => 'Foto Before',
                'foto_after' => 'Foto After',
                'dokumen_pendukung' => 'Dokumen Pendukung',
            ]);

        foreach ($attachments as $fieldKey => $files) {
            foreach ((array) $files as $file) {
                if (! $file) {
                    continue;
                }

                $path = $file->store("qc-submissions/{$submission->id}", 'local');
                $mime = $file->getMimeType();

                $submission->attachments()->create([
                    'field_key' => $fieldKey,
                    'label' => $attachmentLabels[$fieldKey] ?? Str::headline($fieldKey),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                    'type' => 'image',
                ]);
            }
        }
    }

    private function generateFormNumber(): string
    {
        $date = now()->format('Ymd');
        $count = QcFormSubmission::whereDate('created_at', now()->toDateString())->count() + 1;

        return 'QC-'.$date.'-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    private function generateQcDocumentNumber(): string
    {
        $period = now()->format('m-Y');
        $count = QcFormSubmission::where(function ($query) use ($period) {
            $query->where('report_no', 'like', "%/QC/{$period}")
                ->orWhere('form_number', 'like', "%/QC/{$period}");
        })->count() + 1;

        return str_pad((string) $count, 3, '0', STR_PAD_LEFT)."/QC/{$period}";
    }

    private function activeQcMasterDataRecords()
    {
        return MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_QC)
            ->where('status', 'active')
            ->orderBy('func_location')
            ->orderBy('equipment_no')
            ->get();
    }

    private function selectedActiveQcMasterDataRecord(Request $request): ?MasterDataRecord
    {
        $recordId = $request->input('header.master_data_record_id');

        if (! $recordId) {
            return null;
        }

        return MasterDataRecord::query()
            ->whereKey($recordId)
            ->where('document_category', MasterDataRecord::CATEGORY_QC)
            ->where('status', 'active')
            ->first();
    }

    private function authorizeSubmission(QcFormSubmission $submission): void
    {
        abort_unless($submission->user_id === auth()->id() || auth()->user()?->isAdmin(), 403);
    }

    private function attachmentStoragePath(QcFormSubmissionAttachment $attachment): ?string
    {
        if (Storage::disk('local')->exists($attachment->file_path)) {
            return Storage::disk('local')->path($attachment->file_path);
        }

        if (Storage::disk('public')->exists($attachment->file_path)) {
            return Storage::disk('public')->path($attachment->file_path);
        }

        return null;
    }

    private function logStatus(string $event, array $context = []): void
    {
        Log::info($event, $context + [
            'actor_id' => auth()->id(),
            'controller' => self::class,
        ]);
    }

    private function logError(string $code, Throwable $exception, array $context = []): void
    {
        Log::error($code, $context + [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
