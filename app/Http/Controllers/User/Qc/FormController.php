<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormSubmissionAttachment;
use App\Models\QcFormTemplate;
use App\Services\DocumentNumberGenerator;
use App\Services\ApprovalFlowService;
use App\Support\QcTemplates\FixedQcTemplate;
use App\Support\TemplateSnapshot;
use App\Support\UserRoleUiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
    private const ERROR_APPROVAL_LINK = 'QC-APPROVAL-LINK-FAILED';
    private const ALLOWED_ATTACHMENT_MIMES = 'jpg,jpeg,png';
    private const SIGNATURE_MAX_BYTES = 1048576;

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
            'autoDocNumber' => $this->previewQcDocumentNumber(),
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
                $generalInfo = $isFixedTemplate ? $this->fixedHeaderData($request, null, true, $template) : $request->input('general_info', []);
                $bodyData = $isFixedTemplate ? $this->fixedBodyData($request, $template) : null;
                $formNumber = $generalInfo['doc_number'] ?? $generalInfo['report_no'] ?? null;
                $formNumber = $formNumber ?: $this->generateFormNumber();
                $status = $validated['action'] === 'submit' ? 'pending_approval' : 'draft';
                $templateMeta = collect($template->blocks)->pluck('config')->pluck('meta')->filter()->first() ?? [];
                $templateSnapshot = TemplateSnapshot::forQc($template);
                $dateTime = $generalInfo['date_time'] ?? null;

                $submission = QcFormSubmission::create([
                    'qc_form_template_id' => $template->id,
                    'template_code' => $templateSnapshot['code'] ?? null,
                    'template_name' => $templateSnapshot['name'] ?? null,
                    'template_version' => TemplateSnapshot::majorVersion($templateSnapshot['version'] ?? null),
                    'template_snapshot' => $templateSnapshot,
                    'user_id' => $request->user()?->id,
                    'form_number' => $formNumber,
                    'status' => $status,
                    'submitted_at' => $validated['action'] === 'submit' ? now() : null,
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

                $approvalData = $this->approvalDataWithSignatureFiles($request, $request->input('approval', []), $submission);
                $bodyData = $isFixedTemplate ? $this->bodyDataWithSignatureFiles($request, $bodyData ?? [], $submission) : $bodyData;

                $submission->forceFill([
                    'approval_data' => $approvalData,
                    'body_data' => $bodyData,
                ])->save();

                if ($isFixedTemplate) {
                    $this->storeFixedRows($submission, $template, $bodyData ?? []);
                } else {
                    $this->storeRows($submission, $template, $request->input('rows', []));
                }

                $this->storeAttachments($submission, $template, $request->file('attachments', []));

                if ($validated['action'] === 'submit') {
                    app(ApprovalFlowService::class)->startForSubmission($submission, 'qc');
                }

                return $submission;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_STORE, $exception, [
                'template_id' => $template->id,
                'requested_status' => $validated['action'] === 'submit' ? 'pending_approval' : 'draft',
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

        if ($submission->status !== 'draft') {
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

        abort_unless(in_array($submission->status, ['draft', 'revision_required'], true), 403);

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

        abort_unless(in_array($submission->status, ['draft', 'revision_required'], true), 403);

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
                $generalInfo = $isFixedTemplate ? $this->fixedHeaderData($request, $submission->form_number, false, $template) : $request->input('general_info', []);
                $bodyData = $isFixedTemplate ? $this->fixedBodyData($request, $template) : null;
                $formNumber = $generalInfo['doc_number'] ?? $generalInfo['report_no'] ?? null;
                $formNumber = $formNumber ?: $submission->form_number ?: $this->generateFormNumber();
                $status = $validated['action'] === 'submit' ? 'pending_approval' : 'draft';
                $templateMeta = collect($template->blocks)->pluck('config')->pluck('meta')->filter()->first() ?? [];
                $templateSnapshot = $submission->template_snapshot ?: TemplateSnapshot::forQc($template);
                $dateTime = $generalInfo['date_time'] ?? null;

                $approvalData = $this->approvalDataWithSignatureFiles($request, $request->input('approval', []), $submission);
                $bodyData = $isFixedTemplate ? $this->bodyDataWithSignatureFiles($request, $bodyData ?? [], $submission) : $bodyData;

                $submission->update([
                    'template_code' => $submission->template_code ?: ($templateSnapshot['code'] ?? null),
                    'template_name' => $submission->template_name ?: ($templateSnapshot['name'] ?? null),
                    'template_version' => $submission->template_version ?: TemplateSnapshot::majorVersion($templateSnapshot['version'] ?? null),
                    'template_snapshot' => $templateSnapshot,
                    'form_number' => $formNumber,
                    'status' => $status,
                    'submitted_at' => $validated['action'] === 'submit' ? now() : null,
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
                    'approval_data' => $approvalData,
                ]);

                $submission->rows()->delete();

                if ($isFixedTemplate) {
                    $this->storeFixedRows($submission, $template, $bodyData ?? []);
                } else {
                    $this->storeRows($submission, $template, $request->input('rows', []));
                }

                $this->storeAttachments($submission, $template, $request->file('attachments', []));

                if ($validated['action'] === 'submit') {
                    app(ApprovalFlowService::class)->startForSubmission($submission, 'qc');
                }

                return $submission;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, [
                'submission_id' => $submission->id,
                'template_id' => $template->id,
                'requested_status' => $validated['action'] === 'submit' ? 'pending_approval' : 'draft',
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

        if ($submission->status !== 'draft') {
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
        $submission->load(['template.blocks', 'rows', 'attachments', 'user', 'approvalFlow.steps']);

        return view('user.qc.submissions.show', array_merge(UserRoleUiData::qcForm(), [
            'submission' => $submission,
            'statusLabels' => $this->statusLabels(),
            'canCopyApprovalLink' => $submission->status === 'pending_approval'
                && (bool) $submission->approvalFlow?->steps->firstWhere('status', 'active'),
        ]));
    }

    public function approvalLink(QcFormSubmission $submission): JsonResponse
    {
        $this->authorizeSubmission($submission);

        if ($submission->status !== 'pending_approval') {
            return response()->json(['message' => 'Submission tidak sedang menunggu approval.'], 409);
        }

        try {
            $url = app(ApprovalFlowService::class)->getActiveLinkForSubmission($submission);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_APPROVAL_LINK, $exception, ['submission_id' => $submission->id]);

            return response()->json([
                'message' => 'Link approval gagal dibuat. Kode error: '.self::ERROR_APPROVAL_LINK,
            ], 500);
        }

        if (! $url) {
            return response()->json(['message' => 'Tidak ada step approval aktif.'], 404);
        }

        return response()->json(['url' => $url]);
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

        abort_if($submission->status === 'approved', 403);

        $submissionId = $submission->id;
        $redirectRoute = $submission->status === 'draft' ? 'user.qc.drafts.index' : 'user.qc.history.index';

        try {
            app(ApprovalFlowService::class)->cancelFlow($submission, 'Submission deleted by owner');
            $submission->delete();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DESTROY, $exception, ['submission_id' => $submissionId]);

            return back()->withErrors(['submission' => 'Form QC gagal dihapus. Kode error: '.self::ERROR_DESTROY]);
        }

        $this->logStatus('qc_submission_deleted', [
            'submission_id' => $submissionId,
            'status' => 'deleted',
        ]);

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Form QC berhasil dihapus.');
    }

    public static function streamPdf(QcFormSubmission $submission)
    {
        $submission->loadMissing(['template.blocks', 'rows', 'attachments', 'user', 'approvalFlow.steps']);

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
            'pending_approval' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
            'revision_required' => 'Perlu Revisi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
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
            'approval_signature_files' => ['nullable', 'array'],
            'approval_signature_files.*' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'mimetypes:image/png,image/jpeg', 'max:1024'],
            'body_signature_files' => ['nullable', 'array'],
            'body_signature_files.*' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'mimetypes:image/png,image/jpeg', 'max:1024'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['file', 'mimes:'.self::ALLOWED_ATTACHMENT_MIMES, 'mimetypes:image/jpeg,image/png', 'max:10240'],
        ]);
    }

    private function approvalDataWithSignatureFiles(Request $request, mixed $approval, QcFormSubmission $submission): array
    {
        $approvalData = is_array($approval) ? $approval : [];
        $files = $request->file('approval_signature_files', []);

        foreach ($files as $key => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            if (! is_array($approvalData[$key] ?? null)) {
                $approvalData[$key] = [];
            }

            $approvalData[$key]['signature'] = $this->storeUploadedSignature($file, $submission);
        }

        foreach ($approvalData as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $signature = trim((string) ($value['signature'] ?? ''));
            $storedSignature = $this->storeSignatureDataUrlIfNeeded($signature, $submission);

            if ($storedSignature) {
                $approvalData[$key]['signature'] = $storedSignature;
            }
        }

        return $approvalData;
    }

    private function bodyDataWithSignatureFiles(Request $request, array $bodyData, QcFormSubmission $submission): array
    {
        $file = $request->file('body_signature_files.castable_sample_qc_sign_date');

        if (! isset($bodyData['castable_sample']['qc_sign_date']) || ! is_array($bodyData['castable_sample']['qc_sign_date'])) {
            if (! $file instanceof UploadedFile) {
                return $bodyData;
            }

            $bodyData['castable_sample']['qc_sign_date'] = [];
        }

        if ($file instanceof UploadedFile) {
            $bodyData['castable_sample']['qc_sign_date']['signature'] = $this->storeUploadedSignature($file, $submission);

            return $bodyData;
        }

        $signature = trim((string) ($bodyData['castable_sample']['qc_sign_date']['signature'] ?? ''));
        $storedSignature = $this->storeSignatureDataUrlIfNeeded($signature, $submission);

        if ($storedSignature) {
            $bodyData['castable_sample']['qc_sign_date']['signature'] = $storedSignature;
        }

        return $bodyData;
    }

    private function storeUploadedSignature(UploadedFile $file, QcFormSubmission $submission): string
    {
        $extension = $file->getMimeType() === 'image/png' ? 'png' : 'jpg';
        $path = 'signatures/qc/submission-'.$submission->id.'-'.Str::random(16).'.'.$extension;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return Storage::disk('public')->url($path);
    }

    private function storeSignatureDataUrlIfNeeded(string $source, QcFormSubmission $submission): ?string
    {
        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) > self::SIGNATURE_MAX_BYTES || @getimagesizefromstring($binary) === false) {
            return null;
        }

        $extension = $matches[1] === 'png' ? 'png' : 'jpg';
        $path = 'signatures/qc/submission-'.$submission->id.'-'.Str::random(16).'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return Storage::disk('public')->url($path);
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

    private function fixedHeaderData(Request $request, ?string $existingDocNumber = null, bool $forceGeneratedDocNumber = false, ?QcFormTemplate $template = null): array
    {
        $header = collect(FixedQcTemplate::headerFields($template?->template_type))
            ->mapWithKeys(fn ($field) => [$field['key'] => $request->input("header.{$field['key']}")])
            ->all();

        $header['doc_number'] = $existingDocNumber
            ?: ($forceGeneratedDocNumber ? $this->generateQcDocumentNumber() : ($header['doc_number'] ?: null));
        $header['inspector_qc'] = $request->user()?->name;

        if ($masterRecord = $this->selectedActiveQcMasterDataRecord($request)) {
            $header['master_data_record_id'] = $masterRecord->id;
            $header['plant'] = $masterRecord->plant;
            $header['functional_location'] = $masterRecord->func_location;
            $header['tahun'] = $masterRecord->year;
            $header['tag_num'] = $masterRecord->section_no;
            $header['area'] = $masterRecord->area;
            $header['id_equipment'] = $masterRecord->equipment_no;
            $header['name_equipment'] = $masterRecord->description;
        } elseif ($header['date_time'] ?? null) {
            $header['tahun'] = Carbon::parse($header['date_time'])->format('Y');
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

        if ($type === FixedQcTemplate::TYPE_CASTABLE) {
            return [
                'final_check' => (bool) ($body['final_check'] ?? false),
                'castable_customer' => $this->stringMap($body['castable_customer'] ?? []),
                'castable_checks' => collect(FixedQcTemplate::castableInspectionRows())
                    ->mapWithKeys(function ($definition) use ($body) {
                        $row = $body['castable_checks'][$definition['key']] ?? [];
                        $dimensions = $this->castableDimensionValues($row['dimensions'] ?? []);
                        $value = trim((string) ($row['value'] ?? ''));

                        if (($definition['input'] ?? null) === 'dimension') {
                            $filledDimensions = collect($dimensions)->filter(fn ($dimension) => $dimension !== '');
                            $value = $filledDimensions->isNotEmpty()
                                ? $filledDimensions->implode(' x ')
                                : $value;
                        }

                        return [$definition['key'] => [
                            'label' => $definition['label'],
                            'status' => trim((string) ($row['status'] ?? '')),
                            'value' => $value,
                            'dimensions' => $dimensions,
                            'detail' => trim((string) ($row['detail'] ?? '')),
                        ]];
                    })
                    ->all(),
                'castable_sample' => $this->castableSampleData($body['castable_sample'] ?? []),
                'castable_monitoring_type' => trim((string) ($body['castable_monitoring_type'] ?? '')),
                'castable_monitoring_note' => trim((string) ($body['castable_monitoring_note'] ?? '')),
                'castable_monitoring_rows' => $this->castableMonitoringRows($body),
                'castable_monitoring_signatures' => $this->castableMonitoringSignatures($body),
            ];
        }

        if ($type === FixedQcTemplate::TYPE_BRICS) {
            $bricsManpowerRows = $this->bricsManpowerRows($body);

            return [
                'final_check' => (bool) ($body['final_check'] ?? false),
                'brics_customer' => $this->stringMap($body['brics_customer'] ?? []),
                'brics_meta' => $this->stringMap($body['brics_meta'] ?? []),
                'brics_technical' => $this->stringMap($body['brics_technical'] ?? []),
                'brics_manpower' => $this->bricsManpowerMap($bricsManpowerRows),
                'brics_manpower_rows' => $bricsManpowerRows,
                'brics_weather' => $this->stringMap($body['brics_weather'] ?? []),
                'brics_checks' => collect(FixedQcTemplate::bricsInspectionSections())
                    ->flatMap(fn ($section) => $section['items'])
                    ->mapWithKeys(function ($definition) use ($body) {
                        $row = $body['brics_checks'][$definition['key']] ?? [];

                        return [$definition['key'] => [
                            'label' => $definition['label'],
                            'status' => trim((string) ($row['status'] ?? '')),
                            'remark' => trim((string) ($row['remark'] ?? '')),
                        ]];
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
        $headerData = $this->fixedHeaderData($request, null, false, $template);

        foreach (FixedQcTemplate::headerFields($template->template_type) as $field) {
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
                }
            }
        } elseif (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_GENERAL) {
            if (($body['general_rows'] ?? []) === []) {
                $errors['body.general_rows'] = 'Minimal satu row QC Umum wajib diisi.';
            }

            foreach ($body['general_rows'] ?? [] as $index => $row) {
                foreach (['item_pengecekan', 'standar', 'actual', 'status'] as $key) {
                    if (blank($row[$key] ?? null)) {
                        $errors["body.general_rows.{$index}.{$key}"] = 'Item pengecekan, standar, actual, dan status wajib diisi.';
                    }
                }
            }
        } elseif (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_CASTABLE) {
            foreach (FixedQcTemplate::castableInspectionRows() as $definition) {
                $row = $body['castable_checks'][$definition['key']] ?? [];

                if (($definition['input'] ?? null) === 'dimension') {
                    foreach (['length', 'width', 'height'] as $dimensionKey) {
                        $value = $row['dimensions'][$dimensionKey] ?? '';

                        if (blank($value)) {
                            $errors["body.castable_checks.{$definition['key']}.dimensions.{$dimensionKey}"] = "{$definition['label']} wajib diisi angka.";
                        } elseif (! is_numeric($value)) {
                            $errors["body.castable_checks.{$definition['key']}.dimensions.{$dimensionKey}"] = "{$definition['label']} wajib berupa angka.";
                        }
                    }
                } elseif (($definition['input'] ?? null) === 'number') {
                    $value = $row['value'] ?? '';

                    if (blank($value)) {
                        $errors["body.castable_checks.{$definition['key']}.value"] = "{$definition['label']} wajib diisi angka.";
                    } elseif (! is_numeric($value)) {
                        $errors["body.castable_checks.{$definition['key']}.value"] = "{$definition['label']} wajib berupa angka.";
                    }
                }
            }
        } elseif (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_BRICS) {
            foreach ($body['brics_checks'] ?? [] as $key => $row) {
                if (blank($row['status'] ?? null)) {
                    $errors["body.brics_checks.{$key}.status"] = 'Status Brics wajib dipilih.';
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

        if (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_CASTABLE) {
            foreach ($bodyData['castable_checks'] ?? [] as $key => $row) {
                $submission->rows()->create([
                    'block_type' => 'castable_check',
                    'order_no' => (int) (collect(FixedQcTemplate::castableInspectionRows())->firstWhere('key', $key)['no'] ?? 0),
                    'row_data' => $row + ['key' => $key],
                    'status_value' => $row['status'] ?: null,
                    'catatan' => $row['detail'] ?: null,
                    'aktual' => $row['value'] ?: null,
                ]);
            }

            foreach ($bodyData['castable_monitoring_rows'] ?? [] as $index => $row) {
                $submission->rows()->create([
                    'block_type' => 'castable_monitoring',
                    'order_no' => $index + 1,
                    'row_data' => $row,
                    'catatan' => $row['remark'] ?: null,
                    'aktual' => $row['quantity'] ?: null,
                ]);
            }

            return;
        }

        if (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_BRICS) {
            $orderNo = 1;

            foreach (($bodyData['brics_checks'] ?? []) as $key => $row) {
                $submission->rows()->create([
                    'block_type' => 'brics_check',
                    'order_no' => $orderNo++,
                    'row_data' => $row + ['key' => $key],
                    'status_value' => $row['status'] ?: null,
                    'catatan' => $row['remark'] ?: null,
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

    private function stringMap(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => is_scalar($value) ? trim((string) $value) : '')
            ->all();
    }

    private function castableMonitoringRows(array $body): array
    {
        $columns = collect(FixedQcTemplate::castableMonitoringColumns())->pluck('key');

        return collect($body['castable_monitoring_rows'] ?? [])
            ->map(function ($row, $index) use ($columns) {
                $data = [
                    'no' => (string) ($index + 1),
                ];

                foreach ($columns as $key) {
                    $data[$key] = trim((string) ($row[$key] ?? ''));
                }

                return $data;
            })
            ->filter(fn ($row) => collect($row)->except('no')->filter(fn ($value) => $value !== '')->isNotEmpty())
            ->values()
            ->all();
    }

    private function castableDimensionValues(mixed $dimensions): array
    {
        $dimensions = is_array($dimensions) ? $dimensions : [];

        return [
            'length' => trim((string) ($dimensions['length'] ?? '')),
            'width' => trim((string) ($dimensions['width'] ?? '')),
            'height' => trim((string) ($dimensions['height'] ?? '')),
        ];
    }

    private function castableSampleData(array $values): array
    {
        $sample = [];

        foreach (FixedQcTemplate::castableSampleRows() as $row) {
            if ($row['key'] === 'qc_sign_date') {
                $signature = is_array($values['qc_sign_date'] ?? null) ? $values['qc_sign_date'] : [];
                $sample['qc_sign_date'] = [
                    'name' => trim((string) ($signature['name'] ?? '')),
                    'date' => trim((string) ($signature['date'] ?? '')),
                    'signature' => trim((string) ($signature['signature'] ?? '')),
                    'signed_at' => trim((string) ($signature['signed_at'] ?? '')),
                ];

                continue;
            }

            $sample[$row['key']] = is_scalar($values[$row['key']] ?? null)
                ? trim((string) $values[$row['key']])
                : '';
        }

        return $sample;
    }

    private function castableMonitoringSignatures(array $body): array
    {
        return collect(FixedQcTemplate::castableMonitoringSignatures())
            ->mapWithKeys(function ($definition) use ($body) {
                if ((bool) ($definition['locked'] ?? false)) {
                    return [$definition['key'] => [
                        'name' => '',
                        'date' => '',
                        'signature' => '',
                        'role' => $definition['role'],
                        'signed_at' => '',
                    ]];
                }

                $row = $body['castable_monitoring_signatures'][$definition['key']] ?? [];

                return [$definition['key'] => [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'date' => trim((string) ($row['date'] ?? '')),
                    'signature' => trim((string) ($row['signature'] ?? '')),
                    'role' => $definition['role'],
                    'signed_at' => trim((string) ($row['signed_at'] ?? '')),
                ]];
            })
            ->all();
    }

    private function bricsManpowerRows(array $body): array
    {
        $rows = collect($body['brics_manpower_rows'] ?? [])
            ->map(fn ($row) => [
                'left_label' => trim((string) ($row['left_label'] ?? '')),
                'left_value' => trim((string) ($row['left_value'] ?? '')),
                'right_label' => trim((string) ($row['right_label'] ?? '')),
                'right_value' => trim((string) ($row['right_value'] ?? '')),
            ])
            ->filter(fn ($row) => collect($row)->filter(fn ($value) => $value !== '')->isNotEmpty())
            ->values()
            ->all();

        if ($rows !== []) {
            return $rows;
        }

        $legacyManpower = $this->stringMap($body['brics_manpower'] ?? []);

        return collect(FixedQcTemplate::bricsManpowerRows())
            ->map(function (array $row) use ($legacyManpower) {
                $leftLabel = $row['left'];
                $rightLabel = $row['right'];

                return [
                    'left_label' => $leftLabel,
                    'left_value' => $legacyManpower[$this->bricsManpowerKey($leftLabel)] ?? $legacyManpower[Str::snake($leftLabel)] ?? '',
                    'right_label' => $rightLabel,
                    'right_value' => $legacyManpower[$this->bricsManpowerKey($rightLabel)] ?? $legacyManpower[Str::snake($rightLabel)] ?? '',
                ];
            })
            ->all();
    }

    private function bricsManpowerMap(array $rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            foreach (['left', 'right'] as $side) {
                $label = $row["{$side}_label"] ?? '';

                if ($label === '') {
                    continue;
                }

                $map[$this->bricsManpowerKey($label)] = $row["{$side}_value"] ?? '';
            }
        }

        return $map;
    }

    private function bricsManpowerKey(string $label): string
    {
        return Str::slug($label, '_');
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
        return $this->generateQcDocumentNumber();
    }

    private function generateQcDocumentNumber(): string
    {
        $period = now()->format('m-Y');

        return app(DocumentNumberGenerator::class)->generate(
            'qc',
            'QC',
            $period,
            $this->maxExistingQcDocumentNumber($period)
        );
    }

    private function previewQcDocumentNumber(): string
    {
        $period = now()->format('m-Y');

        return app(DocumentNumberGenerator::class)->preview(
            'qc',
            'QC',
            $period,
            $this->maxExistingQcDocumentNumber($period)
        );
    }

    private function maxExistingQcDocumentNumber(string $period): int
    {
        $documents = QcFormSubmission::query()
            ->where(function ($query) use ($period) {
                $query->where('report_no', 'like', "%/QC/{$period}")
                    ->orWhere('form_number', 'like', "%/QC/{$period}");
            })
            ->get(['report_no', 'form_number'])
            ->flatMap(fn (QcFormSubmission $submission) => [
                $submission->report_no,
                $submission->form_number,
            ]);

        return $this->maxDocumentSequence($documents, 'QC', $period);
    }

    private function maxDocumentSequence(iterable $documents, string $prefix, string $period): int
    {
        $pattern = '/^(\d+)\/'.preg_quote($prefix, '/').'\/'.preg_quote($period, '/').'$/';
        $max = 0;

        foreach ($documents as $document) {
            if (preg_match($pattern, trim((string) $document), $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
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
