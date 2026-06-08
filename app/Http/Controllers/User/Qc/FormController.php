<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use App\Models\OrganizationSection;
use App\Models\QcFormSubmission;
use App\Models\QcFormSubmissionAttachment;
use App\Models\QcFormTemplate;
use App\Services\ApprovalFlowService;
use App\Services\DocumentNumberGenerator;
use App\Services\InspectionSubmissionDeletionService;
use App\Services\MasterDataInspectionStatusService;
use App\Services\MasterDataStatusService;
use App\Services\QcPdfAttachmentMerger;
use App\Support\AreaOwnerLabel;
use App\Support\OrganizationSections;
use App\Support\QcTemplates\FixedQcTemplate;
use App\Support\TemplateSnapshot;
use App\Support\UserRoleUiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
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
    private const ERROR_STORE = 'QC-SUB-STORE-FAILED';
    private const ERROR_UPDATE = 'QC-SUB-UPDATE-FAILED';
    private const ERROR_PDF = 'QC-SUB-PDF-FAILED';
    private const ERROR_DESTROY = 'QC-SUB-DESTROY-FAILED';
    private const ERROR_APPROVAL_LINK = 'QC-APPROVAL-LINK-FAILED';
    private const ERROR_DUPLICATE_NUMBER = 'QC-DOC-NUMBER-DUPLICATE';
    private const ERROR_FORBIDDEN = 'QC-SUB-FORBIDDEN';
    private const ERROR_NOT_EDITABLE = 'QC-SUB-NOT-EDITABLE';
    private const ALLOWED_ATTACHMENT_MIMES = 'jpg,jpeg,png,pdf';
    private const SIGNATURE_MAX_BYTES = 1048576;
    private const TEMP_ATTACHMENT_SESSION_KEY = 'qc_temporary_attachments';

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
            'activeOrganizationSections' => $this->activeOrganizationSections(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubmissionRequest($request);

        $template = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
            ->where('status', 'active')
            ->findOrFail($validated['template_id']);

        if ($template->template_type && $validated['action'] === 'submit') {
            try {
                $this->validateFixedSubmission($request, $template);
            } catch (ValidationException $exception) {
                return $this->backWithTemporaryAttachments($request, $exception);
            }
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
                    'approval_data' => [],
                ]);

                $approvalData = $this->approvalDataWithSignatureFiles($request, $request->input('approval', []), $submission);
                $approvalData = $this->normalizedFixedApprovalData($template, $approvalData, $generalInfo['unit_kerja'] ?? null);
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

                $this->storeAttachments($submission, $template, $request->file('attachments', []), $request->input('temporary_attachments', []));
                $this->syncMasterDataInspectionStatus($submission, $request);

                if ($validated['action'] === 'submit') {
                    app(ApprovalFlowService::class)->startForSubmission($submission, 'qc');
                }

                return $submission;
            });
        } catch (Throwable $exception) {
            if ($this->isDuplicateFormNumberException($exception)) {
                $this->logError(self::ERROR_DUPLICATE_NUMBER, $exception, [
                    'template_id' => $template->id,
                    'requested_status' => $validated['action'] === 'submit' ? 'pending_approval' : 'draft',
                ]);

                return $this->backWithDocumentNumberCollision(
                    $request,
                    'Nomor form QC sudah dipakai oleh submission lain. Silakan submit ulang agar sistem membuat nomor terbaru. Kode error: '.self::ERROR_DUPLICATE_NUMBER
                );
            }

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

    public function edit(QcFormSubmission $submission): View|RedirectResponse
    {
        $this->authorizeSubmission($submission);

        if ($redirect = $this->redirectIfSubmissionNotEditable($submission, 'edit')) {
            return $redirect;
        }

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
            'activeOrganizationSections' => $this->activeOrganizationSections(),
        ]));
    }

    public function update(Request $request, QcFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);

        if ($redirect = $this->redirectIfSubmissionNotEditable($submission, 'update')) {
            return $redirect;
        }

        $validated = $this->validateSubmissionRequest($request);
        abort_unless((int) $validated['template_id'] === (int) $submission->qc_form_template_id, 422);

        $template = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
            ->findOrFail($submission->qc_form_template_id);

        if ($template->template_type && $validated['action'] === 'submit') {
            try {
                $this->validateFixedSubmission($request, $template, $submission);
            } catch (ValidationException $exception) {
                return $this->backWithTemporaryAttachments($request, $exception);
            }
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
                $approvalData = $this->normalizedFixedApprovalData($template, $approvalData, $generalInfo['unit_kerja'] ?? null);
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

                $this->deleteRequestedOrReplacedAttachments($submission, $request);
                $this->storeAttachments($submission, $template, $request->file('attachments', []), $request->input('temporary_attachments', []));
                $this->syncMasterDataInspectionStatus($submission, $request);

                if ($validated['action'] === 'submit') {
                    app(ApprovalFlowService::class)->startForSubmission($submission, 'qc');
                }

                return $submission;
            });
        } catch (Throwable $exception) {
            if ($this->isDuplicateFormNumberException($exception)) {
                $this->logError(self::ERROR_DUPLICATE_NUMBER, $exception, [
                    'submission_id' => $submission->id,
                    'template_id' => $template->id,
                    'requested_status' => $validated['action'] === 'submit' ? 'pending_approval' : 'draft',
                ]);

                return $this->backWithDocumentNumberCollision(
                    $request,
                    'Nomor form QC sudah dipakai oleh submission lain. Silakan submit ulang agar sistem membuat nomor terbaru. Kode error: '.self::ERROR_DUPLICATE_NUMBER
                );
            }

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
            app(InspectionSubmissionDeletionService::class)->resetQcMasterStatus($submission, $submission->user);
            $this->deleteAttachmentFiles($submission->attachments);
            $submission->attachments()->delete();
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
        $supportPdfAttachments = self::supportPdfAttachments($submission);

        if ($supportPdfAttachments->isEmpty()) {
            return $pdf->stream($filename);
        }

        $mergedPdf = app(QcPdfAttachmentMerger::class)->merge(
            $pdf->output(),
            $supportPdfAttachments,
            $submission->id,
        );
        $filename = str_replace(['"', "\r", "\n"], '', $filename);

        return response($mergedPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private static function supportPdfAttachments(QcFormSubmission $submission)
    {
        return $submission->attachments
            ->where('field_key', 'dokumen_pendukung')
            ->filter(fn (QcFormSubmissionAttachment $attachment): bool => $attachment->mime_type === 'application/pdf'
                || strtolower(pathinfo($attachment->original_name ?: $attachment->file_path, PATHINFO_EXTENSION)) === 'pdf')
            ->values();
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
        $validated = $request->validate([
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
            'attachments.*.*' => ['file', 'mimes:'.self::ALLOWED_ATTACHMENT_MIMES, 'mimetypes:image/jpeg,image/png,application/pdf', 'max:10240'],
            'temporary_attachments' => ['nullable', 'array'],
            'temporary_attachments.*' => ['nullable', 'array'],
            'temporary_attachments.*.*' => ['string'],
            'remove_existing_attachments' => ['nullable', 'array'],
            'remove_existing_attachments.*' => ['integer'],
        ], [
            'attachments.*.*.uploaded' => 'Lampiran gagal diupload. Ukuran file kemungkinan terlalu besar atau koneksi terputus. Coba pilih file yang lebih kecil.',
            'attachments.*.*.file' => 'Lampiran harus berupa file.',
            'attachments.*.*.max' => 'Lampiran maksimal 10 MB per file.',
            'attachments.*.*.mimes' => 'Lampiran harus berupa JPG, PNG, atau PDF khusus Dokumen Pendukung.',
            'attachments.*.*.mimetypes' => 'Lampiran harus berupa JPG, PNG, atau PDF khusus Dokumen Pendukung.',
        ]);

        $this->validateAttachmentFileTypes($request);

        return $validated;
    }

    private function validateAttachmentFileTypes(Request $request): void
    {
        $errors = [];
        $sessionAttachments = session(self::TEMP_ATTACHMENT_SESSION_KEY, []);

        foreach ($request->file('attachments', []) as $fieldKey => $files) {
            foreach ((array) $files as $index => $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $this->validateAttachmentMimeForField($errors, (string) $fieldKey, $file->getMimeType(), "attachments.{$fieldKey}.{$index}");
            }
        }

        foreach ($request->input('temporary_attachments', []) as $fieldKey => $tokens) {
            foreach ((array) $tokens as $index => $token) {
                $attachment = $sessionAttachments[$token] ?? null;

                if (! is_array($attachment)) {
                    continue;
                }

                $this->validateAttachmentMimeForField($errors, (string) $fieldKey, $attachment['mime_type'] ?? null, "temporary_attachments.{$fieldKey}.{$index}");
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateAttachmentMimeForField(array &$errors, string $fieldKey, ?string $mime, string $errorKey): void
    {
        $allowedMimes = $fieldKey === 'dokumen_pendukung'
            ? ['image/jpeg', 'image/png', 'application/pdf']
            : ['image/jpeg', 'image/png'];

        if (in_array($mime, $allowedMimes, true)) {
            return;
        }

        $errors[$errorKey] = $fieldKey === 'dokumen_pendukung'
            ? 'Dokumen Pendukung hanya boleh JPG, PNG, atau PDF.'
            : 'Foto Before dan Foto After hanya boleh JPG atau PNG.';
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

    private function normalizedFixedApprovalData(QcFormTemplate $template, array $approvalData, ?string $unitKerja = null): array
    {
        if (! $template->template_type) {
            return $approvalData;
        }

        $type = FixedQcTemplate::normalizeType($template->template_type);
        $schema = FixedQcTemplate::schemaForTemplate($template);
        $approvalDefaults = $schema['approval_defaults'] ?? FixedQcTemplate::defaultApprovalDefaults($type);

        return collect(FixedQcTemplate::approvalColumnsWithDefaults($type, $approvalDefaults))
            ->mapWithKeys(function (array $column) use ($type, $approvalData, $approvalDefaults, $unitKerja) {
                $key = $column['key'];
                $approval = is_array($approvalData[$key] ?? null) ? $approvalData[$key] : [];
                $group = FixedQcTemplate::approvalGroupIsEditable($type, $key)
                    ? FixedQcTemplate::approvalEditableValue($type, $key, $approval['group'] ?? '')
                    : $column['group'];
                $label = FixedQcTemplate::approvalLabelIsEditable($type, $key)
                    ? FixedQcTemplate::approvalEditableValue($type, $key, $approval['label'] ?? '')
                    : $column['label'];
                if (
                    in_array($type, [FixedQcTemplate::TYPE_GENERAL, FixedQcTemplate::TYPE_WELDING], true)
                    && ($column['role'] ?? null) === 'Unit Kerja'
                ) {
                    $label = AreaOwnerLabel::approvalLabel($unitKerja, $label);
                }

                return [$key => [
                    'name' => trim((string) ($approval['name'] ?? ($approvalDefaults[$key]['name'] ?? ''))),
                    'date' => trim((string) ($approval['date'] ?? '')),
                    'signature' => trim((string) ($approval['signature'] ?? '')),
                    'role' => $column['role'] ?? $column['label'],
                    'group' => $group,
                    'label' => $label,
                    'signed_at' => trim((string) ($approval['signed_at'] ?? '')),
                ]];
            })
            ->all();
    }

    private function bodyDataWithSignatureFiles(Request $request, array $bodyData, QcFormSubmission $submission): array
    {
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
        $header['department'] = $request->input('header.department');
        $header['work_unit'] = $request->input('header.work_unit');
        $header['organization_section_id'] = $request->input('header.organization_section_id');

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

        if ($type === FixedQcTemplate::TYPE_ELECTRICAL) {
            $schema = FixedQcTemplate::schemaForTemplate($template);

            return [
                'final_check' => (bool) ($body['final_check'] ?? false),
                'electrical_stator_rows' => $this->electricalMeasurementData($schema['stator_rows'] ?? [], $body['electrical_stator_rows'] ?? []),
                'electrical_rotor_rows' => $this->electricalMeasurementData($schema['rotor_rows'] ?? [], $body['electrical_rotor_rows'] ?? []),
                'electrical_ovality_rows' => collect($schema['ovality_rows'] ?? [])->values()->map(function ($templateRow, $index) use ($body) {
                    $row = collect($body['electrical_ovality_rows'] ?? [])->values()->get($index, []);

                    return [
                        'ring' => $templateRow['ring'] ?? '',
                        'standard' => $templateRow['standard'] ?? '',
                        'tir' => trim((string) ($row['tir'] ?? '')),
                    ];
                })->all(),
                'electrical_installation_rows' => collect($schema['installation_rows'] ?? [])->values()->map(function ($templateRow, $index) use ($body) {
                    $row = collect($body['electrical_installation_rows'] ?? [])->values()->get($index, []);

                    return [
                        'activity' => $templateRow['activity'] ?? '',
                        'standard' => $templateRow['standard'] ?? '',
                        'status' => trim((string) ($row['status'] ?? '')),
                        'remark' => trim((string) ($row['remark'] ?? '')),
                    ];
                })->all(),
                'electrical_uncouple_rows' => collect($schema['uncouple_rows'] ?? [])->values()->map(function ($templateRow, $index) use ($body) {
                    $row = collect($body['electrical_uncouple_rows'] ?? [])->values()->get($index, []);

                    return $templateRow + [
                        'value_1' => trim((string) ($row['value_1'] ?? '')),
                        'value_2' => trim((string) ($row['value_2'] ?? '')),
                        'value_3' => trim((string) ($row['value_3'] ?? '')),
                    ];
                })->all(),
            ];
        }

        return [
            'final_check' => (bool) ($body['final_check'] ?? false),
            'general_rows' => array_key_exists('general_rows', $body)
                ? collect(FixedQcTemplate::schemaForTemplate($template)['rows'] ?? [])
                    ->values()
                    ->map(function ($templateRow, $index) use ($body) {
                        $row = collect($body['general_rows'] ?? [])->values()->get($index, []);

                        return [
                            'item_pengecekan' => trim((string) ($templateRow['item_pengecekan'] ?? '')),
                            'standar' => trim((string) ($templateRow['standar'] ?? '')),
                            'status' => $row['status'] ?? null,
                            'catatan' => trim((string) ($row['catatan'] ?? '')),
                        ];
                    })
                    ->filter(fn ($row) => collect($row)->filter()->isNotEmpty())
                    ->values()
                    ->all()
                : [],
        ];
    }

    private function validateFixedSubmission(Request $request, QcFormTemplate $template, ?QcFormSubmission $submission = null): void
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

        $inspectorApprovalColumn = collect(FixedQcTemplate::approvalColumns($template->template_type))
            ->firstWhere('role', 'QC Inspektor');
        $inspectorApprovalKey = $inspectorApprovalColumn['key'] ?? null;

        if ($inspectorApprovalKey && ! $this->hasApprovalSignature($request, $inspectorApprovalKey)) {
            $errors["approval.{$inspectorApprovalKey}.signature"] = 'Tanda tangan QC Inspektor wajib diisi.';
        }

        foreach ($this->requiredFixedAttachmentKeys() as $attachmentKey => $attachmentLabel) {
            if (! $this->hasSubmissionAttachment($request, $attachmentKey, $submission)) {
                $errors["attachments.{$attachmentKey}"] = "{$attachmentLabel} wajib diupload. Dokumen Pendukung boleh dikosongkan.";
            }
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
                foreach (['item_pengecekan', 'standar', 'status'] as $key) {
                    if (blank($row[$key] ?? null)) {
                        $errors["body.general_rows.{$index}.{$key}"] = 'Item pengecekan, standar, dan status wajib diisi.';
                    }
                }

                if (($row['status'] ?? null) === 'Not Ok' && blank($row['catatan'] ?? null)) {
                    $errors["body.general_rows.{$index}.catatan"] = 'Catatan wajib diisi jika status Not Ok.';
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
            foreach (FixedQcTemplate::bricsCustomerRows() as $definition) {
                $key = $definition['key'];

                if (! in_array($key, FixedQcTemplate::requiredBricsCustomerKeys(), true)) {
                    continue;
                }

                if (blank($body['brics_customer'][$key] ?? null)) {
                    $errors["body.brics_customer.{$key}"] = "{$definition['label']} wajib diisi.";
                }
            }

            foreach (FixedQcTemplate::bricsTechnicalRows() as $definition) {
                $key = $definition['key'];

                if (! in_array($key, FixedQcTemplate::requiredBricsTechnicalKeys(), true)) {
                    continue;
                }

                if (blank($body['brics_technical'][$key] ?? null)) {
                    $errors["body.brics_technical.{$key}"] = "{$definition['label']} wajib diisi.";
                }
            }
        } elseif (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_ELECTRICAL) {
            foreach ($body['electrical_installation_rows'] ?? [] as $index => $row) {
                if (! in_array($row['status'] ?? '', ['OK', 'NOT OK'], true)) {
                    $errors["body.electrical_installation_rows.{$index}.status"] = 'Status checklist instalasi wajib dipilih.';
                }

                if (($row['status'] ?? '') === 'NOT OK' && blank($row['remark'] ?? null)) {
                    $errors["body.electrical_installation_rows.{$index}.remark"] = 'Keterangan / Remarks wajib diisi jika status NOT OK.';
                }
            }

        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function hasApprovalSignature(Request $request, string $key): bool
    {
        if ($request->file("approval_signature_files.{$key}") instanceof UploadedFile) {
            return true;
        }

        return filled($request->input("approval.{$key}.signature"));
    }

    private function requiredFixedAttachmentKeys(): array
    {
        return [
            'foto_before' => 'Foto Before',
            'foto_after' => 'Foto After',
        ];
    }

    private function hasSubmissionAttachment(Request $request, string $fieldKey, ?QcFormSubmission $submission = null): bool
    {
        $files = $request->file("attachments.{$fieldKey}", []);

        foreach ((array) $files as $file) {
            if ($file instanceof UploadedFile) {
                return true;
            }
        }

        foreach ((array) $request->input("temporary_attachments.{$fieldKey}", []) as $token) {
            if ($this->temporaryAttachmentExists($token, $fieldKey)) {
                return true;
            }
        }

        $removedIds = $this->attachmentRemovalIds($request);
        $query = $submission?->attachments()->where('field_key', $fieldKey);

        if (! $query) {
            return false;
        }

        if ($removedIds !== []) {
            $query->whereNotIn('id', $removedIds);
        }

        return (bool) $query->exists();
    }

    private function attachmentRemovalIds(Request $request): array
    {
        return collect($request->input('remove_existing_attachments', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function incomingAttachmentFieldKeys(Request $request): array
    {
        $fieldKeys = [];

        foreach ($request->file('attachments', []) as $fieldKey => $files) {
            foreach ((array) $files as $file) {
                if ($file instanceof UploadedFile) {
                    $fieldKeys[] = (string) $fieldKey;
                    break;
                }
            }
        }

        foreach ($request->input('temporary_attachments', []) as $fieldKey => $tokens) {
            foreach ((array) $tokens as $token) {
                if ($this->temporaryAttachmentExists($token, (string) $fieldKey)) {
                    $fieldKeys[] = (string) $fieldKey;
                    break;
                }
            }
        }

        return collect($fieldKeys)->unique()->values()->all();
    }

    private function deleteRequestedOrReplacedAttachments(QcFormSubmission $submission, Request $request): void
    {
        $removedIds = $this->attachmentRemovalIds($request);
        $replacementFields = $this->incomingAttachmentFieldKeys($request);

        if ($removedIds === [] && $replacementFields === []) {
            return;
        }

        $attachments = $submission->attachments()
            ->where(function ($query) use ($removedIds, $replacementFields) {
                if ($removedIds !== []) {
                    $query->whereIn('id', $removedIds);
                }

                if ($replacementFields !== []) {
                    $method = $removedIds !== [] ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('field_key', $replacementFields);
                }
            })
            ->get();

        if ($attachments->isEmpty()) {
            return;
        }

        $this->deleteAttachmentFiles($attachments);
        $submission->attachments()->whereKey($attachments->pluck('id'))->delete();
    }

    private function temporaryAttachmentExists(mixed $token, string $fieldKey): bool
    {
        $attachment = session(self::TEMP_ATTACHMENT_SESSION_KEY.'.'.$token);

        return is_array($attachment)
            && ($attachment['field_key'] ?? null) === $fieldKey
            && Storage::disk('local')->exists($attachment['file_path'] ?? '');
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

        if (FixedQcTemplate::normalizeType($template->template_type) === FixedQcTemplate::TYPE_ELECTRICAL) {
            foreach ([
                'electrical_stator_rows' => 'electrical_stator',
                'electrical_rotor_rows' => 'electrical_rotor',
                'electrical_ovality_rows' => 'electrical_ovality',
                'electrical_installation_rows' => 'electrical_installation',
                'electrical_uncouple_rows' => 'electrical_uncouple',
            ] as $bodyKey => $blockType) {
                foreach ($bodyData[$bodyKey] ?? [] as $index => $row) {
                    $submission->rows()->create([
                        'block_type' => $blockType,
                        'order_no' => $index + 1,
                        'row_data' => $row,
                        'status_value' => $row['status'] ?? null,
                        'catatan' => $row['remark'] ?? null,
                        'aktual' => $row['tir'] ?? $row['value'] ?? $row['value_1'] ?? null,
                    ]);
                }
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
                'aktual' => null,
            ]);
        }
    }

    private function stringMap(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => is_scalar($value) ? trim((string) $value) : '')
            ->all();
    }

    private function electricalMeasurementData(array $templateRows, array $submittedRows): array
    {
        $submittedRows = collect($submittedRows)->values();

        return collect($templateRows)
            ->values()
            ->map(function ($templateRow, $index) use ($submittedRows) {
                $row = $submittedRows->get($index, []);

                return [
                    'item' => $templateRow['item'] ?? '',
                    'value' => trim((string) ($row['value'] ?? '')),
                    'second_30' => trim((string) ($row['second_30'] ?? '')),
                    'minute_1' => trim((string) ($row['minute_1'] ?? '')),
                    'minute_10' => trim((string) ($row['minute_10'] ?? '')),
                    'pi' => trim((string) ($row['pi'] ?? '')),
                ];
            })
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

    private function storeAttachments(QcFormSubmission $submission, QcFormTemplate $template, array $attachments, array $temporaryAttachments = []): void
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
                    'type' => $this->attachmentTypeFromMime($mime),
                ]);
            }
        }

        $this->storeTemporaryAttachments($submission, $attachmentLabels, $temporaryAttachments);
    }

    private function backWithTemporaryAttachments(Request $request, ValidationException $exception): RedirectResponse
    {
        return back()
            ->withInput(array_merge($request->except('attachments', 'temporary_attachments'), [
                'temporary_attachments' => $this->preserveTemporaryAttachments($request),
            ]))
            ->withErrors($exception->errors());
    }

    private function backWithDocumentNumberCollision(Request $request, string $message): RedirectResponse
    {
        return back()
            ->withInput(array_merge($request->except('attachments', 'temporary_attachments'), [
                'temporary_attachments' => $this->preserveTemporaryAttachments($request),
            ]))
            ->withErrors(['form_number' => $message]);
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
                $path = $file->store("temporary-attachments/qc/{$request->user()?->id}", 'local');

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

    private function storeTemporaryAttachments(QcFormSubmission $submission, $attachmentLabels, array $temporaryAttachments): void
    {
        $sessionAttachments = session(self::TEMP_ATTACHMENT_SESSION_KEY, []);

        foreach ($temporaryAttachments as $fieldKey => $tokens) {
            foreach ((array) $tokens as $token) {
                $attachment = $sessionAttachments[$token] ?? null;

                if (! $attachment || ($attachment['field_key'] ?? null) !== $fieldKey || ! Storage::disk('local')->exists($attachment['file_path'])) {
                    continue;
                }

                $targetPath = "qc-submissions/{$submission->id}/".basename($attachment['file_path']);
                Storage::disk('local')->move($attachment['file_path'], $targetPath);

                $submission->attachments()->create([
                    'field_key' => $fieldKey,
                    'label' => $attachmentLabels[$fieldKey] ?? Str::headline($fieldKey),
                    'file_path' => $targetPath,
                    'original_name' => $attachment['original_name'],
                    'mime_type' => $attachment['mime_type'],
                    'size' => $attachment['size'],
                    'type' => $this->attachmentTypeFromMime($attachment['mime_type'] ?? null),
                ]);

                unset($sessionAttachments[$token]);
            }
        }

        session([self::TEMP_ATTACHMENT_SESSION_KEY => $sessionAttachments]);
    }

    private function attachmentTypeFromMime(?string $mime): string
    {
        return str_starts_with((string) $mime, 'image/') ? 'image' : 'document';
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
        $profileAreas = collect(auth()->user()?->profile_areas ?? [])->filter()->values()->all();

        return MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_QC)
            ->when($profileAreas, fn ($query, array $areas) => $query->whereIn('area', $areas))
            ->orderBy('func_location')
            ->orderBy('equipment_no')
            ->get();
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

    private function selectedActiveQcMasterDataRecord(Request $request): ?MasterDataRecord
    {
        $recordId = $request->input('header.master_data_record_id');

        if (! $recordId) {
            return null;
        }

        return MasterDataRecord::query()
            ->whereKey($recordId)
            ->where('document_category', MasterDataRecord::CATEGORY_QC)
            ->when(
                collect(auth()->user()?->profile_areas ?? [])->filter()->values()->all(),
                fn ($query, array $areas) => $query->whereIn('area', $areas)
            )
            ->first();
    }

    private function syncMasterDataInspectionStatus(QcFormSubmission $submission, Request $request): void
    {
        $record = $this->masterDataRecordForSubmission($submission);

        if (! $record) {
            return;
        }

        $previousStatus = $record->status;
        $wasAutoActivated = $previousStatus !== 'active';

        if ($wasAutoActivated) {
            app(MasterDataStatusService::class)->setStatus(
                $record,
                'active',
                MasterDataStatusService::SOURCE_DIGITAL_FORM,
                $request->user(),
                $submission
            );
        }

        app(MasterDataInspectionStatusService::class)->setStatus(
            $record,
            $submission->status === 'draft' ? 'ongoing' : 'close',
            MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
            $request->user(),
            $submission
        );

        if ($wasAutoActivated) {
            $generalInfo = $submission->general_info ?? [];
            $generalInfo['master_data_auto_activated'] = true;
            $generalInfo['master_data_previous_status'] = $previousStatus;

            $submission->forceFill(['general_info' => $generalInfo])->save();
        }
    }

    private function masterDataRecordForSubmission(QcFormSubmission $submission): ?MasterDataRecord
    {
        $header = $submission->general_info ?? [];
        $query = MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_QC);

        if (filled($header['master_data_record_id'] ?? null)) {
            return (clone $query)->whereKey($header['master_data_record_id'])->first();
        }

        if (filled($header['functional_location'] ?? null)) {
            return (clone $query)->where('func_location', $header['functional_location'])->first();
        }

        if (filled($header['id_equipment'] ?? null)) {
            return (clone $query)->where('equipment_no', $header['id_equipment'])->first();
        }

        return null;
    }

    private function authorizeSubmission(QcFormSubmission $submission): void
    {
        $adminPanelRequest = request()->routeIs('admin.*') && auth()->user()?->hasAdminPanelAccess();
        $allowed = (int) $submission->user_id === (int) auth()->id()
            || auth()->user()?->isAdmin()
            || $adminPanelRequest;

        if (! $allowed) {
            $this->logStatus('qc_submission_access_denied', [
                'error_code' => self::ERROR_FORBIDDEN,
                'submission_id' => $submission->id,
                'submission_user_id' => $submission->user_id,
                'status' => $submission->status,
                'route' => request()->route()?->getName(),
                'status_code' => 403,
            ]);

            abort(403);
        }
    }

    private function redirectIfSubmissionNotEditable(QcFormSubmission $submission, string $action): ?RedirectResponse
    {
        if (in_array($submission->status, ['draft', 'revision_required'], true)) {
            return null;
        }

        $this->logStatus('qc_submission_edit_blocked', [
            'error_code' => self::ERROR_NOT_EDITABLE,
            'submission_id' => $submission->id,
            'status' => $submission->status,
            'action' => $action,
            'route' => request()->route()?->getName(),
            'status_code' => 409,
        ]);

        $statusLabel = self::statusLabels()[$submission->status] ?? $submission->status;

        return redirect()
            ->route('user.qc.submissions.show', $submission)
            ->withErrors([
                'submission' => "Draft QC tidak bisa diedit karena statusnya sudah {$statusLabel}. Kode error: ".self::ERROR_NOT_EDITABLE,
            ]);
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

    private function isDuplicateFormNumberException(Throwable $exception): bool
    {
        if (! $exception instanceof QueryException) {
            return false;
        }

        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $message = strtolower($exception->getMessage());

        return in_array($sqlState, ['23000', '23505'], true)
            && str_contains($message, 'form_number');
    }

    private function deleteAttachmentFiles(iterable $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (! $attachment instanceof QcFormSubmissionAttachment || blank($attachment->file_path)) {
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
