<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Support\UserRoleUiData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormController extends Controller
{
    public function create(Request $request): View
    {
        $templates = QcFormTemplate::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedTemplate = null;

        if ($templates->isNotEmpty()) {
            $selectedTemplate = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
                ->where('status', 'active')
                ->when($request->filled('template'), fn ($query) => $query->whereKey($request->input('template')))
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
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:qc_form_templates,id'],
            'action' => ['required', 'in:draft,submit'],
            'general_info' => ['nullable', 'array'],
            'rows' => ['nullable', 'array'],
            'note' => ['nullable', 'string'],
            'approval' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['file', 'max:10240'],
        ]);

        $template = QcFormTemplate::with(['blocks.fields', 'blocks.tableRows', 'fields', 'tableRows'])
            ->where('status', 'active')
            ->findOrFail($validated['template_id']);

        $submission = DB::transaction(function () use ($request, $template, $validated) {
            $generalInfo = $request->input('general_info', []);
            $formNumber = $generalInfo['report_no'] ?? null;
            $formNumber = $formNumber ?: $this->generateFormNumber();
            $status = $validated['action'] === 'submit' ? 'submitted' : 'draft';
            $templateMeta = collect($template->blocks)->pluck('config')->pluck('meta')->filter()->first() ?? [];

            $submission = QcFormSubmission::create([
                'qc_form_template_id' => $template->id,
                'user_id' => $request->user()?->id,
                'form_number' => $formNumber,
                'status' => $status,
                'submitted_at' => $status === 'submitted' ? now() : null,
                'year' => $generalInfo['tahun'] ?? null,
                'plant' => $generalInfo['plant'] ?? $generalInfo['ovh_plant'] ?? null,
                'area' => $generalInfo['area'] ?? null,
                'equipment' => $generalInfo['alat'] ?? $generalInfo['equipment'] ?? ($templateMeta['equipment'] ?? null),
                'report_no' => $generalInfo['report_no'] ?? $formNumber,
                'ovh_plant' => $generalInfo['ovh_plant'] ?? null,
                'unit' => $generalInfo['unit'] ?? null,
                'tag_num' => $generalInfo['tag_num'] ?? null,
                'tgl_mulai' => $generalInfo['tgl_mulai'] ?? null,
                'pekerjaan' => $generalInfo['pekerjaan'] ?? ($templateMeta['pekerjaan'] ?? null),
                'durasi' => $generalInfo['durasi'] ?? null,
                'general_info' => $generalInfo,
                'note' => $request->input('note'),
                'approval_data' => $request->input('approval', []),
            ]);

            $this->storeRows($submission, $template, $request->input('rows', []));
            $this->storeAttachments($submission, $template, $request->file('attachments', []));

            return $submission;
        });

        if ($submission->status === 'submitted') {
            return redirect()
                ->route('user.qc.history.index')
                ->with('success', 'Form QC berhasil disubmit.');
        }

        return redirect()
            ->route('user.qc.drafts.index')
            ->with('success', 'Draft QC berhasil disimpan.');
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

        return $this->streamPdf($submission);
    }

    public function destroy(QcFormSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($submission);

        abort_unless($submission->status === 'draft', 403);

        $submission->delete();

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

        $filename = 'QC-'.$submission->form_number.'.pdf';

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

    private function storeAttachments(QcFormSubmission $submission, QcFormTemplate $template, array $attachments): void
    {
        $attachmentLabels = $template->blocks
            ->where('type', 'attachment')
            ->flatMap(fn ($block) => collect($block->config['fields'] ?? [])->mapWithKeys(fn ($field) => [
                ($field['key'] ?? $field['name'] ?? Str::snake($field['label'] ?? 'lampiran')) => $field['label'] ?? null,
            ]));

        foreach ($attachments as $fieldKey => $files) {
            foreach ((array) $files as $file) {
                if (! $file) {
                    continue;
                }

                $path = $file->store("qc-submissions/{$submission->id}", 'public');
                $mime = $file->getMimeType();

                $submission->attachments()->create([
                    'field_key' => $fieldKey,
                    'label' => $attachmentLabels[$fieldKey] ?? Str::headline($fieldKey),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'size' => $file->getSize(),
                    'type' => str_starts_with((string) $mime, 'image/') ? 'image' : 'file',
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

    private function authorizeSubmission(QcFormSubmission $submission): void
    {
        abort_unless($submission->user_id === auth()->id() || auth()->user()?->isAdmin(), 403);
    }
}
