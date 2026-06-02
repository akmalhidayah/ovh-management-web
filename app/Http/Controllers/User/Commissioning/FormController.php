<?php

namespace App\Http\Controllers\User\Commissioning;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormSubmissionAttachment;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\OrganizationSection;
use App\Services\DocumentNumberGenerator;
use App\Services\ApprovalFlowService;
use App\Services\InspectionSubmissionDeletionService;
use App\Services\MasterDataInspectionStatusService;
use App\Support\Commissioning\FixedCommissioningTemplate;
use App\Support\OrganizationSections;
use App\Support\TemplateSnapshot;
use App\Support\UserRoleUiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class FormController extends Controller
{
    private const ERROR_STORE = 'COM-SUB-STORE-FAILED';
    private const ERROR_UPDATE = 'COM-SUB-UPDATE-FAILED';
    private const ERROR_PDF = 'COM-SUB-PDF-FAILED';
    private const ERROR_APPROVAL_LINK = 'COM-APPROVAL-LINK-FAILED';
    private const ERROR_DESTROY = 'COM-SUB-DESTROY-FAILED';
    private const ALLOWED_ATTACHMENT_MIMES = 'jpg,jpeg,png';
    private const TEMP_ATTACHMENT_SESSION_KEY = 'commissioning_temporary_attachments';
    private const MASTER_DATA_BLOCKING_STATUSES = [
        'draft',
        'submitted',
        'pending_approval',
        'approved',
        'revision',
        'revision_required',
    ];

    public function create(Request $request): View|RedirectResponse
    {
        $templates = CommissioningFormTemplate::where('status', 'active')->orderBy('name')->get();
        $selectedTemplate = null;

        if ($templates->isNotEmpty()) {
            $selectedTemplate = CommissioningFormTemplate::where('status', 'active')
                ->when($request->query('template'), fn ($query, $template) => $query->whereKey($template))
                ->first() ?: $templates->first();
        }

        $activeMasterDataRecords = $this->activeMasterDataRecords();
        $requestedMasterDataId = $request->query('master_data_record_id');

        if ($requestedMasterDataId && ! $activeMasterDataRecords->contains('id', (int) $requestedMasterDataId)) {
            return redirect()
                ->route('user.commissioning.dashboard')
                ->with('warning', 'Equipment tersebut sudah dipakai atau di-close. Silakan pilih equipment lain dari daftar terbaru.');
        }

        return view('user.commissioning.forms.create', array_merge(UserRoleUiData::commissioningForm(), [
            'templates' => $templates,
            'selectedTemplate' => $selectedTemplate,
            'autoDocNumber' => $this->previewDocumentNumber(),
            'activeMasterDataRecords' => $activeMasterDataRecords,
            'activeOrganizationSections' => $this->activeOrganizationSections(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $this->validateMasterDataRecordAvailable($request);

        $template = CommissioningFormTemplate::where('status', 'active')->findOrFail($validated['template_id']);

        if ($validated['action'] === 'submit') {
            try {
                $this->validateSubmit($request, $template);
            } catch (ValidationException $exception) {
                return $this->backWithTemporaryAttachments($request, $exception);
            }
        }

        try {
            $submission = DB::transaction(function () use ($request, $template, $validated) {
                $header = $this->headerData($request, null, true);
                $body = $this->bodyData($request);
                $status = $validated['action'] === 'submit' ? 'pending_approval' : 'draft';
                $templateSnapshot = TemplateSnapshot::forCommissioning($template);

                $submission = CommissioningFormSubmission::create([
                    'commissioning_form_template_id' => $template->id,
                    'template_code' => $templateSnapshot['code'] ?? null,
                    'template_name' => $templateSnapshot['name'] ?? null,
                    'template_version' => TemplateSnapshot::majorVersion($templateSnapshot['version'] ?? null),
                    'template_snapshot' => $templateSnapshot,
                    'user_id' => $request->user()?->id,
                    'form_number' => $header['doc_number'],
                    'status' => $status,
                    'submitted_at' => $validated['action'] === 'submit' ? now() : null,
                    'year' => $header['tahun'] ?? null,
                    'area' => $header['area'] ?? null,
                    'equipment' => $header['name_equipment'] ?? null,
                    'equipment_no' => $header['id_equipment'] ?? null,
                    'tag_num' => $header['tag_num'] ?? null,
                    'functional_location' => $header['functional_location'] ?? null,
                    'header_data' => $header,
                    'body_data' => $body,
                    'note' => $request->input('note'),
                    'approval_data' => $this->normalizedApprovalData($request->input('approval', []), $header['unit_kerja'] ?? null),
                ]);

                $this->storeAttachments($submission, $request->file('attachments', []), $request->input('temporary_attachments', []));
                $this->syncMasterDataInspectionStatus($submission, $request);

                if ($validated['action'] === 'submit') {
                    app(ApprovalFlowService::class)->startForSubmission($submission, 'commissioning');
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
                ->withErrors(['submission' => 'Form Commissioning gagal disimpan. Kode error: '.self::ERROR_STORE]);
        }

        $this->logStatus('commissioning_submission_saved', [
            'submission_id' => $submission->id,
            'template_id' => $template->id,
            'status' => $submission->status,
        ]);

        return redirect()
            ->route($submission->status !== 'draft' ? 'user.commissioning.history.index' : 'user.commissioning.drafts.index')
            ->with('success', $submission->status !== 'draft' ? 'Form Commissioning berhasil disubmit.' : 'Draft Commissioning berhasil disimpan.');
    }

    public function edit(CommissioningFormSubmission $submission): View
    {
        $this->authorizeSubmission($submission);
        abort_unless(in_array($submission->status, ['draft', 'revision_required'], true), 403);

        return view('user.commissioning.forms.create', array_merge(UserRoleUiData::commissioningForm(), [
            'templates' => CommissioningFormTemplate::where('status', 'active')->orderBy('name')->get(),
            'selectedTemplate' => $submission->template,
            'draftSubmission' => $submission,
            'autoDocNumber' => $submission->form_number,
            'activeMasterDataRecords' => $this->activeMasterDataRecords($submission),
            'activeOrganizationSections' => $this->activeOrganizationSections(),
        ]));
    }

    public function update(Request $request, CommissioningFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);
        abort_unless(in_array($submission->status, ['draft', 'revision_required'], true), 403);

        $validated = $this->validateRequest($request);
        abort_unless((int) $validated['template_id'] === (int) $submission->commissioning_form_template_id, 422);
        $this->validateMasterDataRecordAvailable($request, $submission);

        $template = CommissioningFormTemplate::findOrFail($submission->commissioning_form_template_id);

        if ($validated['action'] === 'submit') {
            try {
                $this->validateSubmit($request, $template, $submission);
            } catch (ValidationException $exception) {
                return $this->backWithTemporaryAttachments($request, $exception);
            }
        }

        try {
            DB::transaction(function () use ($request, $submission, $validated, $template) {
                $header = $this->headerData($request, $submission->form_number, false, $submission);
                $status = $validated['action'] === 'submit' ? 'pending_approval' : 'draft';
                $templateSnapshot = $submission->template_snapshot ?: TemplateSnapshot::forCommissioning($template);

                $submission->update([
                    'template_code' => $submission->template_code ?: ($templateSnapshot['code'] ?? null),
                    'template_name' => $submission->template_name ?: ($templateSnapshot['name'] ?? null),
                    'template_version' => $submission->template_version ?: TemplateSnapshot::majorVersion($templateSnapshot['version'] ?? null),
                    'template_snapshot' => $templateSnapshot,
                    'form_number' => $header['doc_number'],
                    'status' => $status,
                    'submitted_at' => $validated['action'] === 'submit' ? now() : null,
                    'year' => $header['tahun'] ?? null,
                    'area' => $header['area'] ?? null,
                    'equipment' => $header['name_equipment'] ?? null,
                    'equipment_no' => $header['id_equipment'] ?? null,
                    'tag_num' => $header['tag_num'] ?? null,
                    'functional_location' => $header['functional_location'] ?? null,
                    'header_data' => $header,
                    'body_data' => $this->bodyData($request),
                    'note' => $request->input('note'),
                    'approval_data' => $this->normalizedApprovalData($request->input('approval', []), $header['unit_kerja'] ?? null),
                ]);

                $this->storeAttachments($submission, $request->file('attachments', []), $request->input('temporary_attachments', []));
                $this->syncMasterDataInspectionStatus($submission, $request);

                if ($validated['action'] === 'submit') {
                    app(ApprovalFlowService::class)->startForSubmission($submission, 'commissioning');
                }
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, [
                'submission_id' => $submission->id,
                'template_id' => $submission->commissioning_form_template_id,
                'requested_status' => $validated['action'] === 'submit' ? 'pending_approval' : 'draft',
            ]);

            return back()
                ->withInput()
                ->withErrors(['submission' => 'Draft Commissioning gagal diperbarui. Kode error: '.self::ERROR_UPDATE]);
        }

        $this->logStatus('commissioning_submission_updated', [
            'submission_id' => $submission->id,
            'template_id' => $submission->commissioning_form_template_id,
            'status' => $submission->status,
        ]);

        return redirect()
            ->route($submission->status !== 'draft' ? 'user.commissioning.history.index' : 'user.commissioning.drafts.index')
            ->with('success', $submission->status !== 'draft' ? 'Form Commissioning berhasil disubmit.' : 'Draft Commissioning berhasil diperbarui.');
    }

    public function show(CommissioningFormSubmission $submission): View
    {
        $this->authorizeSubmission($submission);
        $submission->load(['template', 'attachments', 'approvalFlow.steps']);

        return view('user.commissioning.submissions.show', array_merge(UserRoleUiData::commissioningForm(), [
            'submission' => $submission,
            'canCopyApprovalLink' => $submission->status === 'pending_approval'
                && (bool) $submission->approvalFlow?->steps->firstWhere('status', 'active'),
        ]));
    }

    public function approvalLink(CommissioningFormSubmission $submission): JsonResponse
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

    public function pdf(CommissioningFormSubmission $submission)
    {
        $this->authorizeSubmission($submission);
        $submission->load(['template', 'attachments']);

        try {
            $response = self::streamPdf($submission);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_PDF, $exception, ['submission_id' => $submission->id]);

            return back()->withErrors(['pdf' => 'PDF Commissioning gagal dibuka. Kode error: '.self::ERROR_PDF]);
        }

        $this->logStatus('commissioning_submission_pdf_opened', [
            'submission_id' => $submission->id,
            'status' => $submission->status,
        ]);

        return $response;
    }

    public function attachment(CommissioningFormSubmissionAttachment $attachment)
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

    public function destroy(CommissioningFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);
        abort_if($submission->status === 'approved', 403);

        $submissionId = $submission->id;
        $redirectRoute = $submission->status === 'draft' ? 'user.commissioning.drafts.index' : 'user.commissioning.history.index';

        try {
            app(ApprovalFlowService::class)->cancelFlow($submission, 'Submission deleted by owner');
            app(InspectionSubmissionDeletionService::class)->resetCommissioningMasterStatus($submission, auth()->user());
            $this->deleteAttachmentFiles($submission->attachments);
            $submission->attachments()->delete();
            $submission->delete();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DESTROY, $exception, ['submission_id' => $submissionId]);

            return back()->withErrors(['submission' => 'Form Commissioning gagal dihapus. Kode error: '.self::ERROR_DESTROY]);
        }

        $this->logStatus('commissioning_submission_deleted', [
            'submission_id' => $submissionId,
            'status' => 'deleted',
        ]);

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Form Commissioning berhasil dihapus.');
    }

    public static function streamPdf(CommissioningFormSubmission $submission)
    {
        $submission->loadMissing(['template', 'attachments', 'user', 'approvalFlow.steps']);

        return Pdf::loadView('pdf.commissioning-submission', ['submission' => $submission])
            ->setPaper('a4', 'portrait')
            ->stream('Commissioning - '.Str::slug($submission->form_number).'.pdf');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'template_id' => ['required', 'exists:commissioning_form_templates,id'],
            'action' => ['required', 'in:draft,submit'],
            'header' => ['nullable', 'array'],
            'body' => ['nullable', 'array'],
            'note' => ['nullable', 'string'],
            'approval' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['file', 'mimes:'.self::ALLOWED_ATTACHMENT_MIMES, 'mimetypes:image/jpeg,image/png', 'max:10240'],
            'temporary_attachments' => ['nullable', 'array'],
            'temporary_attachments.*' => ['nullable', 'array'],
            'temporary_attachments.*.*' => ['string'],
        ], [
            'attachments.*.*.uploaded' => 'Foto dokumentasi gagal diupload. Ukuran file kemungkinan terlalu besar atau koneksi terputus. Coba ambil ulang foto atau pilih foto yang lebih kecil.',
            'attachments.*.*.file' => 'Dokumentasi harus berupa file gambar.',
            'attachments.*.*.max' => 'Foto dokumentasi maksimal 10 MB per file.',
            'attachments.*.*.mimes' => 'Foto dokumentasi harus berupa JPG atau PNG.',
            'attachments.*.*.mimetypes' => 'Foto dokumentasi harus berupa JPG atau PNG.',
        ]);
    }

    private function validateSubmit(Request $request, CommissioningFormTemplate $template, ?CommissioningFormSubmission $submission = null): void
    {
        $errors = [];
        $header = $this->headerData($request);
        $body = $this->bodyData($request);
        $schema = FixedCommissioningTemplate::normalizeSchema($template->body_schema);

        foreach (FixedCommissioningTemplate::headerFields() as $field) {
            if ($field['key'] === 'doc_number') {
                continue;
            }

            if (blank($header[$field['key']] ?? null)) {
                $errors["header.{$field['key']}"] = "{$field['label']} wajib diisi.";
            }
        }

        $equipmentRows = $body['equipment_check_rows'] ?? [];
        if ($equipmentRows === []) {
            $errors['body.equipment_check_rows'] = 'Minimal satu equipment check wajib diisi.';
        }

        foreach ($equipmentRows as $index => $row) {
            if (blank($row['item'] ?? null)) {
                $errors["body.equipment_check_rows.{$index}.item"] = 'Item equipment check wajib diisi.';
            }

            if (! ($row['check'] ?? false)) {
                $errors["body.equipment_check_rows.{$index}.check"] = 'Check equipment wajib dicentang.';
            }

            if (blank($row['result'] ?? null)) {
                $errors["body.equipment_check_rows.{$index}.result"] = 'Result equipment wajib dipilih.';
            }

        }

        $hasNewAttachment = collect($request->file('attachments.dokumentasi', []))->filter()->isNotEmpty();
        $hasTemporaryAttachment = collect($request->input('temporary_attachments.dokumentasi', []))
            ->filter(fn ($token) => $this->temporaryAttachmentExists($token, 'dokumentasi'))
            ->isNotEmpty();
        $hasExistingAttachment = $submission?->attachments()->exists() ?? false;
        if (! $hasNewAttachment && ! $hasTemporaryAttachment && ! $hasExistingAttachment) {
            $errors['attachments.dokumentasi'] = 'Dokumentasi wajib diupload. Hanya JPG atau PNG.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function headerData(Request $request, ?string $existingDocNumber = null, bool $forceGeneratedDocNumber = false, ?CommissioningFormSubmission $currentSubmission = null): array
    {
        $header = collect(FixedCommissioningTemplate::headerFields())
            ->mapWithKeys(fn ($field) => [$field['key'] => $request->input("header.{$field['key']}")])
            ->all();

        $header['doc_number'] = $existingDocNumber
            ?: ($forceGeneratedDocNumber ? $this->generateDocumentNumber() : ($header['doc_number'] ?: null));
        $header['inspector_commissioning'] = $request->user()?->name;
        $header['department'] = $request->input('header.department');
        $header['work_unit'] = $request->input('header.work_unit');
        $header['organization_section_id'] = $request->input('header.organization_section_id');

        if ($record = $this->selectedMasterDataRecord($request, $currentSubmission)) {
            $header['master_data_record_id'] = $record->id;
            $header['tahun'] = $record->year;
            $header['plant'] = $record->plant;
            $header['area'] = $record->area;
            $header['tag_num'] = $record->section_no;
            $header['functional_location'] = $record->func_location;
            $header['name_equipment'] = $record->description;
            $header['id_equipment'] = $record->equipment_no;
        }

        return $header;
    }

    private function normalizedApprovalData(mixed $approval, ?string $unitKerja = null): array
    {
        $approvalData = is_array($approval) ? $approval : [];

        foreach (FixedCommissioningTemplate::approvalColumns() as $column) {
            $key = $column['key'];

            if (! is_array($approvalData[$key] ?? null)) {
                $approvalData[$key] = [];
            }

            if ($key === 'unit_kerja') {
                $approvalData[$key]['label'] = trim((string) $unitKerja) ?: $column['label'];
            }
        }

        return $approvalData;
    }

    private function isRemarksField(mixed $key): bool
    {
        return in_array(strtolower(trim((string) $key)), ['remarks', 'remark'], true);
    }

    private function bodyData(Request $request): array
    {
        $body = $request->input('body', []);

        return [
            'motor_rating' => $body['motor_rating'] ?? [],
            'motor_test_rows' => array_values($body['motor_test_rows'] ?? []),
            'gearbox_rating' => $body['gearbox_rating'] ?? [],
            'gearbox_test_rows' => array_values($body['gearbox_test_rows'] ?? []),
            'equipment_check_rows' => collect($body['equipment_check_rows'] ?? [])
                ->map(fn ($row, $index) => [
                    'no' => (string) ($row['no'] ?? $index + 1),
                    'item' => trim((string) ($row['item'] ?? '')),
                    'check' => (bool) ($row['check'] ?? false),
                    'result' => $row['result'] ?? null,
                    'remark' => trim((string) ($row['remark'] ?? '')),
                ])
                ->filter(fn ($row) => $row['item'] !== '' || $row['check'] || $row['result'] || $row['remark'] !== '')
                ->values()
                ->all(),
        ];
    }

    private function storeAttachments(CommissioningFormSubmission $submission, array $attachments, array $temporaryAttachments = []): void
    {
        foreach ($attachments as $fieldKey => $files) {
            foreach ((array) $files as $file) {
                if (! $file) {
                    continue;
                }

                $path = $file->store("commissioning-submissions/{$submission->id}", 'local');
                $mime = $file->getMimeType();

                $submission->attachments()->create([
                    'field_key' => $fieldKey,
                    'label' => Str::headline($fieldKey),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                    'type' => 'image',
                ]);
            }
        }

        $this->storeTemporaryAttachments($submission, $temporaryAttachments);
    }

    private function backWithTemporaryAttachments(Request $request, ValidationException $exception): RedirectResponse
    {
        return back()
            ->withInput(array_merge($request->except('attachments', 'temporary_attachments'), [
                'temporary_attachments' => $this->preserveTemporaryAttachments($request),
            ]))
            ->withErrors($exception->errors());
    }

    private function preserveTemporaryAttachments(Request $request): array
    {
        $sessionAttachments = session(self::TEMP_ATTACHMENT_SESSION_KEY, []);
        $groupedTokens = collect($request->input('temporary_attachments', []))
            ->map(fn ($tokens) => collect((array) $tokens)
                ->filter(fn ($token) => isset($sessionAttachments[$token]))
                ->values()
                ->all())
            ->filter()
            ->all();

        foreach ($request->file('attachments', []) as $fieldKey => $files) {
            foreach ((array) $files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $token = (string) Str::uuid();
                $path = $file->store("temporary-attachments/commissioning/{$request->user()?->id}", 'local');

                $sessionAttachments[$token] = [
                    'field_key' => $fieldKey,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];

                $groupedTokens[$fieldKey][] = $token;
            }
        }

        session([self::TEMP_ATTACHMENT_SESSION_KEY => $sessionAttachments]);

        return $groupedTokens;
    }

    private function temporaryAttachmentExists(mixed $token, string $fieldKey): bool
    {
        $attachment = session(self::TEMP_ATTACHMENT_SESSION_KEY.'.'.$token);

        return is_array($attachment)
            && ($attachment['field_key'] ?? null) === $fieldKey
            && Storage::disk('local')->exists($attachment['file_path'] ?? '');
    }

    private function storeTemporaryAttachments(CommissioningFormSubmission $submission, array $temporaryAttachments): void
    {
        $sessionAttachments = session(self::TEMP_ATTACHMENT_SESSION_KEY, []);

        foreach ($temporaryAttachments as $fieldKey => $tokens) {
            foreach ((array) $tokens as $token) {
                $attachment = $sessionAttachments[$token] ?? null;

                if (! $attachment || ($attachment['field_key'] ?? null) !== $fieldKey || ! Storage::disk('local')->exists($attachment['file_path'])) {
                    continue;
                }

                $targetPath = "commissioning-submissions/{$submission->id}/".basename($attachment['file_path']);
                Storage::disk('local')->move($attachment['file_path'], $targetPath);

                $submission->attachments()->create([
                    'field_key' => $fieldKey,
                    'label' => Str::headline($fieldKey),
                    'file_path' => $targetPath,
                    'original_name' => $attachment['original_name'],
                    'mime_type' => $attachment['mime_type'],
                    'size' => $attachment['size'],
                    'type' => 'image',
                ]);

                unset($sessionAttachments[$token]);
            }
        }

        session([self::TEMP_ATTACHMENT_SESSION_KEY => $sessionAttachments]);
    }

    private function activeMasterDataRecords(?CommissioningFormSubmission $currentSubmission = null)
    {
        $used = $this->usedMasterDataKeys($currentSubmission);
        $profileAreas = collect(auth()->user()?->profile_areas ?? [])->filter()->values()->all();

        return MasterDataRecord::query()
            ->with('organizationSection')
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)
            ->when($profileAreas, fn ($query, array $areas) => $query->whereIn('area', $areas))
            ->orderBy('func_location')
            ->orderBy('equipment_no')
            ->get()
            ->reject(fn (MasterDataRecord $record) => (
                filled($record->inspection_status) && ! $this->currentSubmissionUsesMasterDataRecord($currentSubmission, $record)
            ) || $this->masterDataRecordIsUsed($record, $used))
            ->values();
    }

    private function activeOrganizationSections()
    {
        $sections = OrganizationSection::query()
            ->active()
            ->orderBy('department')
            ->orderBy('unit_kerja')
            ->orderBy('section')
            ->get();

        if ($sections->isNotEmpty() || OrganizationSection::query()->exists()) {
            return $sections;
        }

        return collect(OrganizationSections::rows())
            ->map(fn (array $row) => (object) $row)
            ->values();
    }

    private function selectedMasterDataRecord(Request $request, ?CommissioningFormSubmission $currentSubmission = null): ?MasterDataRecord
    {
        $record = MasterDataRecord::whereKey($request->input('header.master_data_record_id'))
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)
            ->when(
                collect(auth()->user()?->profile_areas ?? [])->filter()->values()->all(),
                fn ($query, array $areas) => $query->whereIn('area', $areas)
            )
            ->first();

        if (
            ! $record
            || (filled($record->inspection_status) && ! $this->currentSubmissionUsesMasterDataRecord($currentSubmission, $record))
            || $this->masterDataRecordIsUsed($record, $this->usedMasterDataKeys($currentSubmission))
        ) {
            return null;
        }

        return $record;
    }

    private function validateMasterDataRecordAvailable(Request $request, ?CommissioningFormSubmission $currentSubmission = null): void
    {
        if (! $request->input('header.master_data_record_id')) {
            return;
        }

        if ($this->selectedMasterDataRecord($request, $currentSubmission)) {
            return;
        }

        throw ValidationException::withMessages([
            'header.master_data_record_id' => 'Equipment commissioning ini sudah dipakai pada form lain atau tidak aktif.',
        ]);
    }

    private function usedMasterDataKeys(?CommissioningFormSubmission $currentSubmission = null): array
    {
        $used = [
            'ids' => [],
            'functional_locations' => [],
            'equipment_nos' => [],
        ];

        CommissioningFormSubmission::query()
            ->when($currentSubmission, fn ($query) => $query->whereKeyNot($currentSubmission->id))
            ->whereIn('status', self::MASTER_DATA_BLOCKING_STATUSES)
            ->get(['header_data', 'functional_location', 'equipment_no'])
            ->each(function (CommissioningFormSubmission $submission) use (&$used) {
                $header = $submission->header_data ?? [];

                if (filled($header['master_data_record_id'] ?? null)) {
                    $used['ids'][] = (string) $header['master_data_record_id'];
                }

                if (filled($submission->functional_location)) {
                    $used['functional_locations'][] = (string) $submission->functional_location;
                }

                if (filled($submission->equipment_no)) {
                    $used['equipment_nos'][] = (string) $submission->equipment_no;
                }
            });

        return array_map(fn (array $values) => array_values(array_unique($values)), $used);
    }

    private function masterDataRecordIsUsed(MasterDataRecord $record, array $used): bool
    {
        return in_array((string) $record->id, $used['ids'], true)
            || in_array((string) $record->func_location, $used['functional_locations'], true)
            || (filled($record->equipment_no) && in_array((string) $record->equipment_no, $used['equipment_nos'], true));
    }

    private function currentSubmissionUsesMasterDataRecord(?CommissioningFormSubmission $submission, MasterDataRecord $record): bool
    {
        if (! $submission) {
            return false;
        }

        $header = $submission->header_data ?? [];

        return (filled($header['master_data_record_id'] ?? null) && (string) $header['master_data_record_id'] === (string) $record->id)
            || (filled($submission->functional_location) && (string) $submission->functional_location === (string) $record->func_location)
            || (filled($submission->equipment_no) && filled($record->equipment_no) && (string) $submission->equipment_no === (string) $record->equipment_no);
    }

    private function syncMasterDataInspectionStatus(CommissioningFormSubmission $submission, Request $request): void
    {
        $record = $this->masterDataRecordForSubmission($submission);

        if (! $record) {
            return;
        }

        $previousStatus = $record->status;
        $wasAutoActivated = $previousStatus !== 'active';

        if ($wasAutoActivated) {
            $record->forceFill(['status' => 'active'])->save();
        }

        app(MasterDataInspectionStatusService::class)->setStatus(
            $record,
            $submission->status === 'draft' ? 'ongoing' : 'close',
            MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
            $request->user(),
            $submission
        );

        if ($wasAutoActivated) {
            $header = $submission->header_data ?? [];
            $header['master_data_auto_activated'] = true;
            $header['master_data_previous_status'] = $previousStatus;

            $submission->forceFill(['header_data' => $header])->save();
        }
    }

    private function masterDataRecordForSubmission(CommissioningFormSubmission $submission): ?MasterDataRecord
    {
        $header = $submission->header_data ?? [];
        $query = MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING);

        if (filled($header['master_data_record_id'] ?? null)) {
            return (clone $query)->whereKey($header['master_data_record_id'])->first();
        }

        if (filled($submission->functional_location)) {
            return (clone $query)->where('func_location', $submission->functional_location)->first();
        }

        if (filled($submission->equipment_no)) {
            return (clone $query)->where('equipment_no', $submission->equipment_no)->first();
        }

        return null;
    }

    private function generateDocumentNumber(): string
    {
        $period = now()->format('m-Y');

        return app(DocumentNumberGenerator::class)->generate(
            'commissioning',
            'COM',
            $period,
            $this->maxExistingDocumentNumber($period)
        );
    }

    private function previewDocumentNumber(): string
    {
        $period = now()->format('m-Y');

        return app(DocumentNumberGenerator::class)->preview(
            'commissioning',
            'COM',
            $period,
            $this->maxExistingDocumentNumber($period)
        );
    }

    private function maxExistingDocumentNumber(string $period): int
    {
        $documents = CommissioningFormSubmission::query()
            ->where('form_number', 'like', "%/COM/{$period}")
            ->pluck('form_number');

        $pattern = '/^(\d+)\/COM\/'.preg_quote($period, '/').'$/';
        $max = 0;

        foreach ($documents as $document) {
            if (preg_match($pattern, trim((string) $document), $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }

    private function authorizeSubmission(CommissioningFormSubmission $submission): void
    {
        abort_unless((int) $submission->user_id === (int) auth()->id() || auth()->user()?->isAdmin(), 403);
    }

    private function attachmentStoragePath(CommissioningFormSubmissionAttachment $attachment): ?string
    {
        if (Storage::disk('local')->exists($attachment->file_path)) {
            return Storage::disk('local')->path($attachment->file_path);
        }

        if (Storage::disk('public')->exists($attachment->file_path)) {
            return Storage::disk('public')->path($attachment->file_path);
        }

        return null;
    }

    private function deleteAttachmentFiles(iterable $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (! $attachment instanceof CommissioningFormSubmissionAttachment || blank($attachment->file_path)) {
                continue;
            }

            Storage::disk('local')->delete($attachment->file_path);
            Storage::disk('public')->delete($attachment->file_path);
        }
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
