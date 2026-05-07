<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QcFormTemplate;
use App\Models\QcFormTemplateBlock;
use App\Support\QcTemplates\TemplateBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class TemplateFormQcController extends Controller
{
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
        ]);

        return view('admin.template-form-qc.create', [
            'template' => $template,
            'blocks' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);

        $template = DB::transaction(function () use ($request, $validated) {
            $template = QcFormTemplate::create($validated + [
                'created_by' => $request->user()?->id,
            ]);

            $this->syncBlocks($template, $request->input('blocks', []));

            return $template;
        });

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

        DB::transaction(function () use ($request, $template, $validated) {
            $template->update($validated);
            $template->blocks()->delete();
            $this->syncBlocks($template, $request->input('blocks', []));
        });

        return redirect()
            ->route('admin.template-form-qc.edit', $template)
            ->with('success', 'Template Form QC berhasil diperbarui.');
    }

    public function destroy(QcFormTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()
            ->route('admin.template-form-qc.index')
            ->with('success', 'Template Form QC berhasil dihapus.');
    }

    public function preview(QcFormTemplate $template): View
    {
        $template->load(['blocks', 'blocks.fields', 'blocks.tableRows', 'fields', 'tableRows', 'gridCells']);

        return view('admin.template-form-qc.preview', compact('template'));
    }

    public function import(): View
    {
        return view('admin.template-form-qc.import');
    }

    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $file = $request->file('excel_file');
            $storedPath = $file->store('template-form-qc/imports');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = min($sheet->getHighestDataRow(), 250);
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $template = DB::transaction(function () use ($file, $storedPath, $sheet, $highestRow, $highestColumnIndex, $request) {
                $template = QcFormTemplate::create([
                    'code' => null,
                    'name' => Str::headline(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                    'category' => 'QC',
                    'description' => 'Draft hasil import Excel. Silakan review dan rapikan bagian template sebelum dipublish.',
                    'version' => '1.0',
                    'status' => 'draft',
                    // Import Excel lama bersifat semi-auto. Admin harus review sebelum publish.
                    'layout_mode' => 'block_based',
                    'source_file' => $storedPath,
                    'created_by' => $request->user()?->id,
                ]);

                $this->createGeneralInfoBlock($template, 1);
                $header = $this->detectChecklistHeader($sheet, $highestRow, $highestColumnIndex);

                if ($header) {
                    $block = $template->blocks()->create([
                        'type' => 'checklist_table',
                        'title' => 'Checklist QC',
                        'order_no' => 2,
                        'config' => ['columns' => ['No', 'Aktivitas', 'Standar', 'Aktual', 'Keterangan']],
                    ]);

                    $rows = $this->extractChecklistRows($sheet, $header['row'], $header['map'], $highestRow);
                    foreach ($rows as $index => $row) {
                        $block->tableRows()->create([
                            'qc_form_template_id' => $template->id,
                            'order_no' => $index + 1,
                            'row_data' => $row,
                        ]);
                    }
                }

                $this->createApprovalBlock($template, $template->blocks()->count() + 1);

                return $template;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['excel_file' => 'File Excel gagal diproses. Pastikan format file valid dan tidak sedang terkunci.']);
        }

        return redirect()
            ->route('admin.template-form-qc.edit', $template)
            ->with('success', 'Draft template berhasil dibuat. Silakan review dan rapikan sebelum dipublish.');
    }

    public function duplicate(QcFormTemplate $template): RedirectResponse
    {
        $template->load(['blocks.fields', 'blocks.tableRows', 'gridCells']);

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

        return redirect()
            ->route('admin.template-form-qc.edit', $copy)
            ->with('success', 'Template berhasil diduplikasi sebagai draft.');
    }

    public function toggleStatus(QcFormTemplate $template): RedirectResponse
    {
        $template->update([
            'status' => $template->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Status template berhasil diperbarui.');
    }

    public function publish(QcFormTemplate $template): RedirectResponse
    {
        $template->update(['status' => 'active']);

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
            'layout_mode' => ['nullable', Rule::in(['block_based', 'excel_grid', 'excel_like'])],
        ]);

        $validated['version'] = ($validated['version'] ?? null) ?: '1.0';
        $validated['layout_mode'] = ($validated['layout_mode'] ?? 'block_based') === 'excel_like'
            ? 'block_based'
            : ($validated['layout_mode'] ?? 'block_based');

        return $validated;
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

    private function detectChecklistHeader($sheet, int $highestRow, int $highestColumnIndex): ?array
    {
        $required = ['no', 'aktivitas', 'standar', 'aktual', 'keterangan'];

        for ($row = 1; $row <= $highestRow; $row++) {
            $map = [];

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $value = Str::lower(trim((string) $sheet->getCell([$col, $row])->getFormattedValue()));
                $value = str_replace('aktifitas', 'aktivitas', $value);

                if (in_array($value, $required, true)) {
                    $map[$value] = $col;
                }
            }

            if (count(array_intersect($required, array_keys($map))) >= 4) {
                return ['row' => $row, 'map' => $map];
            }
        }

        return null;
    }

    private function extractChecklistRows($sheet, int $headerRow, array $map, int $highestRow): array
    {
        $rows = [];

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $activity = trim((string) $sheet->getCell([$map['aktivitas'] ?? 2, $row])->getFormattedValue());
            $standard = trim((string) $sheet->getCell([$map['standar'] ?? 3, $row])->getFormattedValue());

            if ($activity === '' && $standard === '') {
                if ($rows !== []) {
                    break;
                }

                continue;
            }

            $rows[] = [
                'no' => trim((string) $sheet->getCell([$map['no'] ?? 1, $row])->getFormattedValue()) ?: (string) (count($rows) + 1),
                'aktivitas' => $activity,
                'standar' => $standard,
                'actual_type' => 'text',
                'keterangan' => trim((string) $sheet->getCell([$map['keterangan'] ?? 5, $row])->getFormattedValue()),
            ];
        }

        return $rows;
    }
}
