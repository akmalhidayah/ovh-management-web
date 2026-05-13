<?php

namespace App\Http\Controllers\User\Commissioning;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormSubmissionAttachment;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Support\Commissioning\FixedCommissioningTemplate;
use App\Support\UserRoleUiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    private const ALLOWED_ATTACHMENT_MIMES = 'jpg,jpeg,png';

    public function create(Request $request): View
    {
        $templates = CommissioningFormTemplate::where('status', 'active')->orderBy('name')->get();
        $selectedTemplate = null;

        if ($templates->isNotEmpty()) {
            $selectedTemplate = CommissioningFormTemplate::where('status', 'active')
                ->when($request->query('template'), fn ($query, $template) => $query->whereKey($template))
                ->first() ?: $templates->first();
        }

        return view('user.commissioning.forms.create', array_merge(UserRoleUiData::commissioningForm(), [
            'templates' => $templates,
            'selectedTemplate' => $selectedTemplate,
            'autoDocNumber' => $this->generateDocumentNumber(),
            'activeMasterDataRecords' => $this->activeMasterDataRecords(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $template = CommissioningFormTemplate::where('status', 'active')->findOrFail($validated['template_id']);

        if ($validated['action'] === 'submit') {
            $this->validateSubmit($request, $template);
        }

        try {
            $submission = DB::transaction(function () use ($request, $template, $validated) {
                $header = $this->headerData($request, null, true);
                $body = $this->bodyData($request);
                $status = $validated['action'] === 'submit' ? 'submitted' : 'draft';

                $submission = CommissioningFormSubmission::create([
                    'commissioning_form_template_id' => $template->id,
                    'user_id' => $request->user()?->id,
                    'form_number' => $header['doc_number'],
                    'status' => $status,
                    'submitted_at' => $status === 'submitted' ? now() : null,
                    'year' => $header['tahun'] ?? null,
                    'area' => $header['area'] ?? null,
                    'equipment' => $header['name_equipment'] ?? null,
                    'equipment_no' => $header['id_equipment'] ?? null,
                    'tag_num' => $header['tag_num'] ?? null,
                    'functional_location' => $header['functional_location'] ?? null,
                    'header_data' => $header,
                    'body_data' => $body,
                    'note' => $request->input('note'),
                    'approval_data' => $request->input('approval', []),
                ]);

                $this->storeAttachments($submission, $request->file('attachments', []));

                return $submission;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_STORE, $exception, [
                'template_id' => $template->id,
                'requested_status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
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
            ->route($submission->status === 'submitted' ? 'user.commissioning.history.index' : 'user.commissioning.drafts.index')
            ->with('success', $submission->status === 'submitted' ? 'Form Commissioning berhasil disubmit.' : 'Draft Commissioning berhasil disimpan.');
    }

    public function edit(CommissioningFormSubmission $submission): View
    {
        $this->authorizeSubmission($submission);
        abort_unless($submission->status === 'draft', 403);

        return view('user.commissioning.forms.create', array_merge(UserRoleUiData::commissioningForm(), [
            'templates' => CommissioningFormTemplate::where('status', 'active')->orderBy('name')->get(),
            'selectedTemplate' => $submission->template,
            'draftSubmission' => $submission,
            'autoDocNumber' => $submission->form_number,
            'activeMasterDataRecords' => $this->activeMasterDataRecords(),
        ]));
    }

    public function update(Request $request, CommissioningFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);
        abort_unless($submission->status === 'draft', 403);

        $validated = $this->validateRequest($request);
        abort_unless((int) $validated['template_id'] === (int) $submission->commissioning_form_template_id, 422);

        $template = CommissioningFormTemplate::findOrFail($submission->commissioning_form_template_id);

        if ($validated['action'] === 'submit') {
            $this->validateSubmit($request, $template, $submission);
        }

        try {
            DB::transaction(function () use ($request, $submission, $validated) {
                $header = $this->headerData($request, $submission->form_number);
                $status = $validated['action'] === 'submit' ? 'submitted' : 'draft';

                $submission->update([
                    'form_number' => $header['doc_number'],
                    'status' => $status,
                    'submitted_at' => $status === 'submitted' ? now() : null,
                    'year' => $header['tahun'] ?? null,
                    'area' => $header['area'] ?? null,
                    'equipment' => $header['name_equipment'] ?? null,
                    'equipment_no' => $header['id_equipment'] ?? null,
                    'tag_num' => $header['tag_num'] ?? null,
                    'functional_location' => $header['functional_location'] ?? null,
                    'header_data' => $header,
                    'body_data' => $this->bodyData($request),
                    'note' => $request->input('note'),
                    'approval_data' => $request->input('approval', []),
                ]);

                $this->storeAttachments($submission, $request->file('attachments', []));
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, [
                'submission_id' => $submission->id,
                'template_id' => $submission->commissioning_form_template_id,
                'requested_status' => $validated['action'] === 'submit' ? 'submitted' : 'draft',
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
            ->route($submission->status === 'submitted' ? 'user.commissioning.history.index' : 'user.commissioning.drafts.index')
            ->with('success', $submission->status === 'submitted' ? 'Form Commissioning berhasil disubmit.' : 'Draft Commissioning berhasil diperbarui.');
    }

    public function show(CommissioningFormSubmission $submission): View
    {
        $this->authorizeSubmission($submission);
        $submission->load(['template', 'attachments']);

        return view('user.commissioning.submissions.show', array_merge(UserRoleUiData::commissioningForm(), [
            'submission' => $submission,
        ]));
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

    public static function streamPdf(CommissioningFormSubmission $submission)
    {
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

        foreach ($schema['motor_rating_fields'] as $field) {
            if (blank($body['motor_rating'][$field['key']] ?? null)) {
                $errors["body.motor_rating.{$field['key']}"] = "{$field['label']} wajib diisi.";
            }
        }

        $motorRows = $body['motor_test_rows'] ?? [];
        if ($motorRows === []) {
            $errors['body.motor_test_rows'] = 'Minimal satu row motor test wajib diisi.';
        }

        foreach ($motorRows as $index => $row) {
            foreach ($schema['motor_test_fields'] as $field) {
                if ($this->isRemarksField($field['key'] ?? null)) {
                    continue;
                }

                if (blank($row[$field['key']] ?? null)) {
                    $errors["body.motor_test_rows.{$index}.{$field['key']}"] = "{$field['label']} motor test wajib diisi.";
                }
            }
        }

        foreach ($schema['gearbox_rating_fields'] as $field) {
            if (blank($body['gearbox_rating'][$field['key']] ?? null)) {
                $errors["body.gearbox_rating.{$field['key']}"] = "{$field['label']} gearbox wajib diisi.";
            }
        }

        $gearboxRows = $body['gearbox_test_rows'] ?? [];
        if ($gearboxRows === []) {
            $errors['body.gearbox_test_rows'] = 'Minimal satu row gearbox test wajib diisi.';
        }

        foreach ($gearboxRows as $index => $row) {
            foreach ($schema['gearbox_test_fields'] as $field) {
                if ($this->isRemarksField($field['key'] ?? null)) {
                    continue;
                }

                if (blank($row[$field['key']] ?? null)) {
                    $errors["body.gearbox_test_rows.{$index}.{$field['key']}"] = "{$field['label']} gearbox test wajib diisi.";
                }
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
        $hasExistingAttachment = $submission?->attachments()->exists() ?? false;
        if (! $hasNewAttachment && ! $hasExistingAttachment) {
            $errors['attachments.dokumentasi'] = 'Dokumentasi wajib diupload. Hanya JPG atau PNG.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function headerData(Request $request, ?string $existingDocNumber = null, bool $forceGeneratedDocNumber = false): array
    {
        $header = collect(FixedCommissioningTemplate::headerFields())
            ->mapWithKeys(fn ($field) => [$field['key'] => $request->input("header.{$field['key']}")])
            ->all();

        $header['doc_number'] = $existingDocNumber
            ?: ($forceGeneratedDocNumber ? $this->generateDocumentNumber() : ($header['doc_number'] ?: $this->generateDocumentNumber()));
        $header['inspector_commissioning'] = $request->user()?->name;

        if ($record = $this->selectedMasterDataRecord($request)) {
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

    private function storeAttachments(CommissioningFormSubmission $submission, array $attachments): void
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
    }

    private function activeMasterDataRecords()
    {
        return MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)
            ->where('status', 'active')
            ->orderBy('func_location')
            ->orderBy('equipment_no')
            ->get();
    }

    private function selectedMasterDataRecord(Request $request): ?MasterDataRecord
    {
        return MasterDataRecord::whereKey($request->input('header.master_data_record_id'))
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)
            ->where('status', 'active')
            ->first();
    }

    private function generateDocumentNumber(): string
    {
        $period = now()->format('m-Y');
        $count = CommissioningFormSubmission::where('form_number', 'like', "%/COM/{$period}")->count() + 1;

        return str_pad((string) $count, 3, '0', STR_PAD_LEFT)."/COM/{$period}";
    }

    private function authorizeSubmission(CommissioningFormSubmission $submission): void
    {
        abort_unless($submission->user_id === auth()->id() || auth()->user()?->isAdmin(), 403);
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
