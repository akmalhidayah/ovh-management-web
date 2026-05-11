<?php

namespace App\Support\QcTemplates;

use App\Models\QcFormTemplate;
use App\Models\QcFormTemplateBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateBuilder
{
    public static function createExcelGridTemplate(array $data): QcFormTemplate
    {
        return self::createTemplate($data);
    }

    public static function createTemplate(array $data): QcFormTemplate
    {
        return DB::transaction(function () use ($data) {
            $layoutMode = $data['layout_mode'] ?? 'block_based';
            $templateType = FixedQcTemplate::normalizeType($data['template_type'] ?? FixedQcTemplate::TYPE_GENERAL);
            $bodySchema = $data['body_schema'] ?? self::bodySchemaFromPreset($data);

            $template = QcFormTemplate::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'category' => $data['category'] ?? 'QC',
                    'description' => $data['description'] ?? 'Preset template QC berbasis block.',
                    'version' => $data['version'] ?? '1.0',
                    'status' => $data['status'] ?? 'draft',
                    'layout_mode' => $layoutMode === 'excel_like' ? 'block_based' : $layoutMode,
                    'template_type' => $templateType,
                    'body_schema' => FixedQcTemplate::normalizeSchema($templateType, $bodySchema),
                    'created_by' => $data['created_by'] ?? null,
                ]
            );

            $template->blocks()->delete();
            $template->gridCells()->delete();

            foreach (array_values($data['blocks'] ?? self::defaultBlocks($data)) as $index => $blockData) {
                $block = $template->blocks()->create([
                    'type' => $blockData['type'] ?? 'note',
                    'title' => $blockData['title'] ?? self::defaultTitle($blockData['type'] ?? 'note'),
                    'order_no' => $index + 1,
                    'config' => self::blockConfig($blockData, $data),
                ]);

                self::createFields($template, $block, $blockData);
                self::createRows($template, $block, $blockData);
            }

            return $template->refresh();
        });
    }

    private static function blockConfig(array $blockData, array $templateData): array
    {
        $type = $blockData['type'] ?? 'note';
        $columns = $blockData['columns'] ?? null;

        if ($type === 'approval') {
            $columns = self::normalizeApprovalColumns($columns);
        }

        return array_filter([
            'number' => $templateData['number'] ?? null,
            'meta' => $templateData['meta'] ?? [],
            'columns' => $columns,
            'fields' => $blockData['fields'] ?? null,
            'description' => $blockData['description'] ?? null,
            'notes' => $type === 'approval' ? ($blockData['notes'] ?? self::defaultApprovalNotes()) : ($blockData['notes'] ?? null),
        ], fn ($value) => $value !== null);
    }

    private static function createFields(QcFormTemplate $template, QcFormTemplateBlock $block, array $blockData): void
    {
        $fields = $blockData['fields'] ?? [];

        if (isset($blockData['field'])) {
            $fields[] = $blockData['field'];
        }

        if ($fields === [] && $block->type === 'approval') {
            $fields = $block->config['columns'] ?? self::defaultApprovalColumns();
        }

        foreach (array_values($fields) as $index => $field) {
            if (! is_array($field)) {
                $field = $block->type === 'approval'
                    ? self::normalizeApprovalColumn($field)
                    : ['key' => Str::snake((string) $field), 'label' => (string) $field, 'type' => 'text'];
            }

            $options = $field['options'] ?? null;

            if (array_key_exists('default', $field)) {
                $options = array_merge(is_array($options) ? $options : [], ['default' => $field['default']]);
            }

            foreach (['accept', 'multiple', 'max_files'] as $optionKey) {
                if (array_key_exists($optionKey, $field)) {
                    $options = array_merge(is_array($options) ? $options : [], [$optionKey => $field[$optionKey]]);
                }
            }

            $block->fields()->create([
                'qc_form_template_id' => $template->id,
                'field_name' => $field['name'] ?? $field['key'] ?? 'field_'.$index,
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

    private static function createRows(QcFormTemplate $template, QcFormTemplateBlock $block, array $blockData): void
    {
        if (! in_array($block->type, ['checklist_table', 'measurement_table'], true)) {
            return;
        }

        foreach (array_values($blockData['rows'] ?? []) as $index => $row) {
            $block->tableRows()->create([
                'qc_form_template_id' => $template->id,
                'order_no' => $index + 1,
                'row_data' => $row,
            ]);
        }
    }

    private static function defaultBlocks(array $data): array
    {
        $meta = $data['meta'] ?? [];

        return [
            [
                'type' => 'general_info',
                'title' => 'Informasi Umum',
                'fields' => self::generalInfoFields($meta),
            ],
            [
                'type' => 'checklist_table',
                'title' => 'Item Pengecekan',
                'columns' => self::defaultChecklistColumns(),
                'rows' => self::flattenChecklist($data['checklist'] ?? []),
            ],
            [
                'type' => 'note',
                'title' => 'Catatan',
                'field' => ['name' => 'catatan_umum', 'label' => 'Catatan', 'type' => 'textarea'],
            ],
            [
                'type' => 'approval',
                'title' => 'Approval',
                'columns' => self::defaultApprovalColumns(),
                'notes' => self::defaultApprovalNotes(),
            ],
        ];
    }

    public static function generalInfoFields(array $meta): array
    {
        return [
            ['name' => 'report_no', 'label' => 'Report No.', 'type' => 'text'],
            ['name' => 'ovh_plant', 'label' => 'OVH Plant', 'type' => 'text'],
            ['name' => 'tahun', 'label' => 'Tahun', 'type' => 'number'],
            ['name' => 'unit', 'label' => 'Unit', 'type' => 'text'],
            ['name' => 'alat', 'label' => 'Alat', 'type' => 'text', 'default' => $meta['equipment'] ?? null],
            ['name' => 'tag_num', 'label' => 'Tag Num.', 'type' => 'text'],
            ['name' => 'tgl_mulai', 'label' => 'Tgl. Mulai', 'type' => 'date'],
            ['name' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text', 'default' => $meta['pekerjaan'] ?? null],
            ['name' => 'durasi', 'label' => 'Durasi', 'type' => 'text'],
        ];
    }

    public static function defaultChecklistColumns(): array
    {
        return [
            ['key' => 'kategori', 'label' => 'Kategori', 'type' => 'text', 'readonly' => true],
            ['key' => 'item', 'label' => 'Item Pengecekan', 'type' => 'text', 'readonly' => true],
            ['key' => 'standar', 'label' => 'Standar', 'type' => 'text', 'readonly' => true],
            ['key' => 'status', 'label' => 'Status', 'type' => 'radio', 'options' => ['OK', 'Not OK']],
            ['key' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
        ];
    }

    public static function defaultApprovalColumns(): array
    {
        return [
            ['key' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
            ['key' => 'diisi', 'label' => '*1 Diisi', 'type' => 'signature'],
            ['key' => 'disetujui_1', 'label' => '*2 Disetujui', 'type' => 'signature_locked'],
            ['key' => 'disetujui_2', 'label' => '*3 Disetujui', 'type' => 'signature_locked'],
        ];
    }

    public static function defaultApprovalNotes(): array
    {
        return [
            '*1 Supervisor/Inspector pekerjaan',
            '*2 Manager/atasan supervisor/inspector',
            '*3 Manager bidang terkait (maint mekanikal/electrical atau production support dll)',
        ];
    }

    public static function normalizeApprovalColumns(mixed $columns): array
    {
        if (is_string($columns)) {
            $columns = array_values(array_filter(array_map('trim', explode(',', $columns))));
        }

        if (! is_array($columns) || $columns === []) {
            return self::defaultApprovalColumns();
        }

        return array_map(
            fn ($column) => self::normalizeApprovalColumn($column),
            array_values($columns)
        );
    }

    public static function normalizeApprovalColumn(mixed $column): array
    {
        if (is_array($column)) {
            $label = $column['label'] ?? $column['key'] ?? 'Approval';

            return [
                'key' => $column['key'] ?? Str::snake($label),
                'label' => $label,
                'type' => $column['type'] ?? 'signature_locked',
            ] + array_diff_key($column, array_flip(['key', 'label', 'type']));
        }

        $label = trim((string) $column) ?: 'Approval';

        return [
            'key' => Str::snake($label),
            'label' => $label,
            'type' => 'signature_locked',
        ];
    }

    private static function flattenChecklist(array $checklist): array
    {
        $rows = [];

        foreach ($checklist as $group) {
            foreach (($group['items'] ?? []) as $item) {
                $rows[] = [
                    'kategori' => $group['group'] ?? '',
                    'item' => $item['item'] ?? '',
                    'standar' => $item['standar'] ?? $item['standard'] ?? '',
                ];
            }
        }

        return $rows;
    }

    private static function defaultTitle(string $type): string
    {
        return match ($type) {
            'general_info' => 'Informasi Umum',
            'checklist_table' => 'Item Pengecekan',
            'measurement_table' => 'Measurement',
            'attachment' => 'Lampiran',
            'approval' => 'Approval',
            default => 'Catatan',
        };
    }

    private static function bodySchemaFromPreset(array $data): array
    {
        $rows = [];

        foreach ($data['blocks'] ?? [] as $block) {
            if (! in_array($block['type'] ?? null, ['checklist_table', 'measurement_table'], true)) {
                continue;
            }

            foreach ($block['rows'] ?? [] as $row) {
                $rows[] = [
                    'item_pengecekan' => $row['item_pengecekan'] ?? $row['item'] ?? $row['activity'] ?? $row['aktivitas'] ?? $row['parameter'] ?? '',
                    'standar' => $row['standar'] ?? $row['standard'] ?? '',
                    'actual_default' => $row['actual_default'] ?? $row['actual'] ?? $row['aktual'] ?? '',
                    'urutan' => count($rows) + 1,
                ];
            }
        }

        return ['rows' => $rows];
    }
}
