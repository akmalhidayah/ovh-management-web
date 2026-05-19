<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QcFormTemplate;
use App\Models\QcFormTemplateBlock;
use App\Support\QcTemplates\FixedQcTemplate;
use App\Support\QcTemplates\TemplateBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class TemplateFormQcController extends Controller
{
    private const ERROR_STORE = 'QC-TPL-STORE-FAILED';
    private const ERROR_UPDATE = 'QC-TPL-UPDATE-FAILED';
    private const ERROR_DUPLICATE = 'QC-TPL-DUPLICATE-FAILED';
    private const ERROR_TOGGLE = 'QC-TPL-TOGGLE-FAILED';
    private const ERROR_PUBLISH = 'QC-TPL-PUBLISH-FAILED';
    private const ERROR_DESTROY = 'QC-TPL-DESTROY-FAILED';

    public function index(Request $request): View
    {
        $status = $request->string('status', 'all')->toString();

        $templates = QcFormTemplate::query()
            ->withCount(['blocks', 'fields'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'active' => QcFormTemplate::active()->count(),
            'draft' => QcFormTemplate::draft()->count(),
            'inactive' => QcFormTemplate::where('status', 'inactive')->count(),
            'total' => QcFormTemplate::count(),
        ];

        return view('admin.template-form-qc.index', compact('templates', 'summary'));
    }

    public function create(): View
    {
        $template = new QcFormTemplate([
            'version' => '1.0',
            'status' => 'draft',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_GENERAL,
            'body_schema' => FixedQcTemplate::defaultSchema(FixedQcTemplate::TYPE_GENERAL),
        ]);

        return view('admin.template-form-qc.create', [
            'template' => $template,
            'blocks' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        try {
            $template = DB::transaction(function () use ($request, $validated) {
                $template = QcFormTemplate::create($validated + [
                    'created_by' => $request->user()?->id,
                ]);

                $this->syncFixedBodyBlocks($template);

                return $template;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_STORE, $exception, ['name' => $validated['name'] ?? null]);

            return back()
                ->withInput()
                ->withErrors(['template' => 'Template Form QC gagal dibuat. Kode error: '.self::ERROR_STORE]);
        }

        $this->logStatus('qc_template_created', [
            'template_id' => $template->id,
            'status' => $template->status,
            'template_type' => $template->template_type,
        ]);

        return redirect()
            ->route('admin.template-form-qc.preview', $template)
            ->with('success', 'Template Form QC berhasil dibuat.');
    }

    public function show(QcFormTemplate $template): View
    {
        $template->load(['blocks.fields', 'blocks.tableRows', 'fields', 'gridCells']);

        return view('admin.template-form-qc.show', compact('template'));
    }

    public function edit(QcFormTemplate $template): View
    {
        $template->load(['blocks.tableRows']);

        return view('admin.template-form-qc.edit', [
            'template' => $template,
            'blocks' => $template->blocks,
        ]);
    }

    public function update(Request $request, QcFormTemplate $template): RedirectResponse
    {
        $validated = $this->validateTemplate($request, $template);

        try {
            DB::transaction(function () use ($template, $validated) {
                $template->update($validated);
                $template->blocks()->delete();
                $this->syncFixedBodyBlocks($template);
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, ['template_id' => $template->id]);

            return back()
                ->withInput()
                ->withErrors(['template' => 'Template Form QC gagal diperbarui. Kode error: '.self::ERROR_UPDATE]);
        }

        $this->logStatus('qc_template_updated', [
            'template_id' => $template->id,
            'status' => $template->status,
            'template_type' => $template->template_type,
        ]);

        return redirect()
            ->route('admin.template-form-qc.edit', $template)
            ->with('success', 'Template Form QC berhasil diperbarui.');
    }

    public function destroy(QcFormTemplate $template): RedirectResponse
    {
        $templateId = $template->id;

        try {
            $template->delete();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DESTROY, $exception, ['template_id' => $templateId]);

            return back()->withErrors(['template' => 'Template Form QC gagal dihapus. Kode error: '.self::ERROR_DESTROY]);
        }

        $this->logStatus('qc_template_deleted', [
            'template_id' => $templateId,
            'status' => 'deleted',
        ]);

        return redirect()
            ->route('admin.template-form-qc.index')
            ->with('success', 'Template Form QC berhasil dihapus.');
    }

    public function preview(QcFormTemplate $template): View
    {
        $template->load(['blocks', 'blocks.fields', 'blocks.tableRows', 'fields', 'tableRows', 'gridCells']);

        return view('admin.template-form-qc.preview', compact('template'));
    }

    public function duplicate(QcFormTemplate $template): RedirectResponse
    {
        $template->load(['blocks.fields', 'blocks.tableRows', 'gridCells']);

        try {
            $copy = DB::transaction(function () use ($template) {
                $copy = $template->replicate(['code', 'status']);
                $copy->name = 'Copy - '.$template->name;
                $copy->code = null;
                $copy->status = 'draft';
                $copy->created_by = auth()->id();
                $copy->save();

                $blockMap = [];

                foreach ($template->blocks as $block) {
                    $newBlock = $block->replicate();
                    $newBlock->qc_form_template_id = $copy->id;
                    $newBlock->save();
                    $blockMap[$block->id] = $newBlock->id;

                    foreach ($block->fields as $field) {
                        $newField = $field->replicate();
                        $newField->qc_form_template_id = $copy->id;
                        $newField->qc_form_template_block_id = $newBlock->id;
                        $newField->save();
                    }

                    foreach ($block->tableRows as $row) {
                        $newRow = $row->replicate();
                        $newRow->qc_form_template_id = $copy->id;
                        $newRow->qc_form_template_block_id = $newBlock->id;
                        $newRow->save();
                    }
                }

                foreach ($template->gridCells as $cell) {
                    $newCell = $cell->replicate();
                    $newCell->qc_form_template_id = $copy->id;
                    $newCell->qc_form_template_block_id = $blockMap[$cell->qc_form_template_block_id] ?? null;
                    $newCell->save();
                }

                return $copy;
            });
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DUPLICATE, $exception, ['template_id' => $template->id]);

            return back()->withErrors(['template' => 'Template Form QC gagal diduplikasi. Kode error: '.self::ERROR_DUPLICATE]);
        }

        $this->logStatus('qc_template_duplicated', [
            'source_template_id' => $template->id,
            'template_id' => $copy->id,
            'status' => $copy->status,
        ]);

        return redirect()
            ->route('admin.template-form-qc.edit', $copy)
            ->with('success', 'Template berhasil diduplikasi sebagai draft.');
    }

    public function toggleStatus(QcFormTemplate $template): RedirectResponse
    {
        try {
            $template->update([
                'status' => $template->status === 'active' ? 'inactive' : 'active',
            ]);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_TOGGLE, $exception, ['template_id' => $template->id]);

            return back()->withErrors(['template' => 'Status template Form QC gagal diperbarui. Kode error: '.self::ERROR_TOGGLE]);
        }

        $this->logStatus('qc_template_status_changed', [
            'template_id' => $template->id,
            'status' => $template->status,
        ]);

        return back()->with('success', 'Status template berhasil diperbarui.');
    }

    public function publish(QcFormTemplate $template): RedirectResponse
    {
        try {
            $template->update(['status' => 'active']);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_PUBLISH, $exception, ['template_id' => $template->id]);

            return back()->withErrors(['template' => 'Template Form QC gagal dipublish. Kode error: '.self::ERROR_PUBLISH]);
        }

        $this->logStatus('qc_template_published', [
            'template_id' => $template->id,
            'status' => $template->status,
        ]);

        return back()->with('success', 'Template berhasil dipublish dan sudah bisa digunakan user QC.');
    }

    private function validateTemplate(Request $request, ?QcFormTemplate $template = null): array
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:255', Rule::unique('qc_form_templates', 'code')->ignore($template?->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'version' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'template_type' => ['required', Rule::in(array_keys(FixedQcTemplate::types()))],
            'approval_defaults' => ['nullable', 'array'],
        ]);

        $validated['version'] = ($validated['version'] ?? null) ?: '1.0';
        $validated['layout_mode'] = 'block_based';
        $validated['template_type'] = FixedQcTemplate::normalizeType($validated['template_type'] ?? null);
        $validated['code'] = $this->resolveTemplateCode($validated['code'] ?? null, $template);
        $validated['body_schema'] = FixedQcTemplate::normalizeSchema(
            $validated['template_type'],
            $this->bodySchemaFromRequest($request, $validated['template_type'])
        );

        return $validated;
    }

    private function resolveTemplateCode(?string $code, ?QcFormTemplate $template = null): ?string
    {
        $manualSegment = $this->templateCodeManualSegment($code);

        if ($manualSegment === null) {
            return null;
        }

        $currentManualSegment = $this->templateCodeManualSegment($template?->code);

        if ($template && $template->code && $manualSegment === $currentManualSegment) {
            return $template->code;
        }

        return sprintf('QCR-%s-%03d', $manualSegment, $this->nextTemplateCodeNumber($manualSegment, $template));
    }

    private function templateCodeManualSegment(?string $code): ?string
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        if (preg_match('/^QCR-(.+)-\d+$/i', $code, $matches) === 1) {
            $code = $matches[1];
        }

        $manualSegment = Str::upper(Str::slug($code, '-'));

        return $manualSegment !== '' ? $manualSegment : null;
    }

    private function nextTemplateCodeNumber(string $manualSegment, ?QcFormTemplate $template = null): int
    {
        $codes = QcFormTemplate::withTrashed()
            ->where('code', 'like', "QCR-{$manualSegment}-%")
            ->when($template, fn ($query) => $query->whereKeyNot($template->id))
            ->pluck('code');

        $highest = $codes
            ->map(function (?string $code) use ($manualSegment) {
                if (preg_match('/^QCR-'.preg_quote($manualSegment, '/').'-(\d+)$/i', (string) $code, $matches) !== 1) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return $highest + 1;
    }

    private function bodySchemaFromRequest(Request $request, string $templateType): array
    {
        if ($templateType === FixedQcTemplate::TYPE_WELDING) {
            return [
                'welder_rows' => $request->input('welding_welder_rows', []),
                'result_rows' => $request->input('welding_result_rows', []),
                'approval_defaults' => $request->input('approval_defaults', []),
            ];
        }

        if (FixedQcTemplate::isLockedBodyType($templateType)) {
            return [
                'approval_defaults' => $request->input('approval_defaults', []),
            ];
        }

        return [
            'rows' => $request->input('general_rows', []),
            'approval_defaults' => $request->input('approval_defaults', []),
        ];
    }

    private function syncFixedBodyBlocks(QcFormTemplate $template): void
    {
        $schema = FixedQcTemplate::normalizeSchema($template->template_type, $template->body_schema);

        if ($template->template_type === FixedQcTemplate::TYPE_WELDING) {
            $welderBlock = $template->blocks()->create([
                'type' => 'welding_welder_table',
                'title' => 'Tabel Welder',
                'order_no' => 1,
                'config' => ['fixed' => true],
            ]);

            foreach ($schema['welder_rows'] ?? [] as $index => $row) {
                $welderBlock->tableRows()->create([
                    'qc_form_template_id' => $template->id,
                    'order_no' => $index + 1,
                    'row_data' => $row,
                ]);
            }

            $resultBlock = $template->blocks()->create([
                'type' => 'welding_result_table',
                'title' => 'Tabel Hasil QC Welding',
                'order_no' => 2,
                'config' => ['fixed' => true],
            ]);

            foreach ($schema['result_rows'] ?? [] as $index => $row) {
                $resultBlock->tableRows()->create([
                    'qc_form_template_id' => $template->id,
                    'order_no' => $index + 1,
                    'row_data' => $row,
                ]);
            }

            return;
        }

        if (FixedQcTemplate::isLockedBodyType($template->template_type)) {
            $template->blocks()->create([
                'type' => $template->template_type.'_fixed_body',
                'title' => FixedQcTemplate::templateTypeLabel($template->template_type),
                'order_no' => 1,
                'config' => ['fixed' => true],
            ]);

            return;
        }

        $block = $template->blocks()->create([
            'type' => 'qc_general_body',
            'title' => 'Body QC Umum',
            'order_no' => 1,
            'config' => ['fixed' => true],
        ]);

        foreach ($schema['rows'] ?? [] as $index => $row) {
            $block->tableRows()->create([
                'qc_form_template_id' => $template->id,
                'order_no' => $index + 1,
                'row_data' => $row,
            ]);
        }
    }

    private function syncBlocks(QcFormTemplate $template, array $blocks): void
    {
        if ($blocks === []) {
            $this->createGeneralInfoBlock($template, 1);
            $this->createApprovalBlock($template, 2);

            return;
        }

        foreach (array_values($blocks) as $index => $blockData) {
            $type = $blockData['type'] ?? 'note';
            $block = $template->blocks()->create([
                'type' => $type,
                'title' => $blockData['title'] ?? $this->defaultBlockTitle($type),
                'order_no' => $index + 1,
                'config' => $this->blockConfig($blockData),
            ]);

            $this->createBlockFields($template, $block);
            $this->createBlockRows($template, $block, $blockData['rows'] ?? []);
        }
    }

    private function createBlockFields(QcFormTemplate $template, QcFormTemplateBlock $block): void
    {
        $fields = [];
        $config = $block->config ?? [];

        if (isset($config['fields']) && is_array($config['fields'])) {
            $fields = $config['fields'];
        }

        if (isset($config['field']) && is_array($config['field'])) {
            $fields[] = $config['field'];
        }

        if ($fields === [] && $block->type === 'approval' && isset($config['columns'])) {
            $fields = is_array($config['columns']) ? $config['columns'] : [];
        }

        if ($fields === []) {
            $fields = match ($block->type) {
            'general_info', 'header_grid' => [
                ['field_name' => 'tahun', 'label' => 'Tahun', 'type' => 'text', 'required' => true],
                ['field_name' => 'plant', 'label' => 'Plant', 'type' => 'text', 'required' => true],
                ['field_name' => 'area', 'label' => 'Area', 'type' => 'text', 'required' => true],
                ['field_name' => 'equipment', 'label' => 'Equipment', 'type' => 'text', 'required' => true],
                ['field_name' => 'tanggal_pemeriksaan', 'label' => 'Tanggal Pemeriksaan', 'type' => 'date', 'required' => true],
                ['field_name' => 'qc_personil', 'label' => 'QC Personil', 'type' => 'text', 'required' => true],
            ],
            'note' => [
                ['field_name' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
            ],
            'attachment' => [
                ['field_name' => 'lampiran', 'label' => 'Lampiran Foto/Dokumen', 'type' => 'file'],
            ],
            'approval' => [
                ['field_name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
                ['field_name' => 'diisi', 'label' => '*1 Diisi', 'type' => 'signature'],
                ['field_name' => 'disetujui_1', 'label' => '*2 Disetujui', 'type' => 'signature_locked'],
                ['field_name' => 'disetujui_2', 'label' => '*3 Disetujui', 'type' => 'signature_locked'],
            ],
            default => [],
            };
        }

        foreach ($fields as $index => $field) {
            $options = $field['options'] ?? null;

            if (array_key_exists('default', $field)) {
                $options = array_merge(is_array($options) ? $options : [], ['default' => $field['default']]);
            }

            $block->fields()->create([
                'qc_form_template_id' => $template->id,
                'field_name' => $field['field_name'] ?? $field['name'] ?? $field['key'] ?? 'field_'.$index,
                'label' => $field['label'] ?? $field['name'] ?? $field['key'] ?? 'Field '.($index + 1),
                'type' => $field['type'] ?? 'text',
                'required' => (bool) ($field['required'] ?? false),
                'readonly' => (bool) ($field['readonly'] ?? false),
                'options' => $options,
                'unit' => $field['unit'] ?? null,
                'help_text' => $field['help_text'] ?? null,
                'validation_rules' => $field['validation_rules'] ?? null,
                'order_no' => $index + 1,
            ]);
        }
    }

    private function createBlockRows(QcFormTemplate $template, QcFormTemplateBlock $block, array $rows): void
    {
        if (! in_array($block->type, ['checklist_table', 'measurement_table'], true)) {
            return;
        }

        foreach (array_values($rows) as $index => $row) {
            $rowData = array_filter($row, fn ($value) => $value !== null && $value !== '');

            if ($rowData === []) {
                continue;
            }

            $block->tableRows()->create([
                'qc_form_template_id' => $template->id,
                'order_no' => $index + 1,
                'row_data' => $rowData,
            ]);
        }
    }

    private function createGeneralInfoBlock(QcFormTemplate $template, int $orderNo): void
    {
        $block = $template->blocks()->create([
            'type' => 'general_info',
            'title' => 'Informasi Umum',
            'order_no' => $orderNo,
            'config' => ['columns' => ['Label', 'Input']],
        ]);

        $this->createBlockFields($template, $block);
    }

    private function createApprovalBlock(QcFormTemplate $template, int $orderNo): void
    {
        $block = $template->blocks()->create([
            'type' => 'approval',
            'title' => 'Approval',
            'order_no' => $orderNo,
            'config' => [
                'columns' => TemplateBuilder::defaultApprovalColumns(),
                'notes' => TemplateBuilder::defaultApprovalNotes(),
            ],
        ]);

        $this->createBlockFields($template, $block);
    }

    private function blockConfig(array $blockData): array
    {
        $type = $blockData['type'] ?? 'note';
        $columns = $blockData['columns'] ?? null;

        if (is_string($columns)) {
            $columns = array_values(array_filter(array_map('trim', explode(',', $columns))));
        }

        if ($type === 'approval') {
            $columns = TemplateBuilder::normalizeApprovalColumns($columns);
        } elseif (is_array($columns)) {
            $columns = array_map(fn ($column) => $this->normalizeColumn($column), $columns);
        }

        return [
            'columns' => $columns ?: $this->defaultColumns($type),
            'fields' => $blockData['fields'] ?? null,
            'field' => $blockData['field'] ?? null,
            'notes' => $type === 'approval' ? ($blockData['notes'] ?? TemplateBuilder::defaultApprovalNotes()) : ($blockData['notes'] ?? null),
        ];
    }

    private function defaultBlockTitle(string $type): string
    {
        return match ($type) {
            'general_info' => 'Informasi Umum',
            'header_grid' => 'Header Dokumen',
            'checklist_table' => 'Checklist QC',
            'measurement_table' => 'Measurement',
            'attachment' => 'Lampiran',
            'approval' => 'Approval',
            default => 'Catatan',
        };
    }

    private function defaultColumns(string $type): array
    {
        return match ($type) {
            'checklist_table' => [
                ['key' => 'kategori', 'label' => 'Kategori', 'type' => 'text', 'readonly' => true],
                ['key' => 'item', 'label' => 'Item Pengecekan', 'type' => 'text', 'readonly' => true],
                ['key' => 'standar', 'label' => 'Standar', 'type' => 'text', 'readonly' => true],
                ['key' => 'status', 'label' => 'Status', 'type' => 'radio', 'options' => ['OK', 'Not OK']],
                ['key' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
            ],
            'measurement_table' => [
                ['key' => 'parameter', 'label' => 'Parameter', 'type' => 'text'],
                ['key' => 'standar', 'label' => 'Standar', 'type' => 'text'],
                ['key' => 'aktual', 'label' => 'Aktual', 'type' => 'text'],
                ['key' => 'unit', 'label' => 'Unit', 'type' => 'text'],
                ['key' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
            ],
            'approval' => [
                ...TemplateBuilder::defaultApprovalColumns(),
            ],
            default => [
                ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                ['key' => 'input', 'label' => 'Input', 'type' => 'text'],
            ],
        };
    }

    private function normalizeColumn(array|string $column): array
    {
        if (is_array($column)) {
            return $column;
        }

        $label = trim($column);
        $key = match (Str::lower($label)) {
            'kategori' => 'kategori',
            'item pengecekan', 'item', 'aktivitas' => 'item',
            'standar', 'standard' => 'standar',
            'status', 'aktual', 'actual' => 'status',
            'catatan', 'keterangan', 'note' => 'catatan',
            default => Str::snake($label),
        };

        return [
            'key' => $key,
            'label' => $label,
            'type' => $key === 'status' ? 'radio' : ($key === 'catatan' ? 'textarea' : 'text'),
            'options' => $key === 'status' ? ['OK', 'Not OK'] : null,
        ];
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
