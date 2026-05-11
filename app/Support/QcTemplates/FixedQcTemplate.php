<?php

namespace App\Support\QcTemplates;

use App\Models\QcFormTemplate;
use Illuminate\Support\Arr;

class FixedQcTemplate
{
    public const TYPE_GENERAL = 'general';
    public const TYPE_WELDING = 'welding';

    public static function types(): array
    {
        return [
            self::TYPE_GENERAL => 'QC Umum',
            self::TYPE_WELDING => 'QC Welding',
        ];
    }

    public static function headerFields(): array
    {
        return [
            ['key' => 'doc_number', 'label' => 'Doc.Number', 'type' => 'text'],
            ['key' => 'functional_location', 'label' => 'Functional Location', 'type' => 'text'],
            ['key' => 'tahun', 'label' => 'Tahun', 'type' => 'text'],
            ['key' => 'date_time', 'label' => 'Date & Time', 'type' => 'datetime-local'],
            ['key' => 'tag_num', 'label' => 'Tag.Num', 'type' => 'text'],
            ['key' => 'area', 'label' => 'Area', 'type' => 'text'],
            ['key' => 'name_equipment', 'label' => 'Name Equipment', 'type' => 'text'],
            ['key' => 'id_equipment', 'label' => 'ID Equipment', 'type' => 'text'],
            ['key' => 'alat', 'label' => 'Alat', 'type' => 'text'],
            ['key' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text'],
            ['key' => 'unit_kerja', 'label' => 'Unit Kerja', 'type' => 'text'],
            ['key' => 'durasi', 'label' => 'Durasi', 'type' => 'text'],
        ];
    }

    public static function approvalGroups(): array
    {
        return [
            'qc_inspector' => [
                'heading' => 'Tanda Tangan QC Inspektor',
                'roles' => ['QC Inspektor'],
            ],
            'checked_by' => [
                'heading' => 'Checked by / Diperiksa Oleh:',
                'roles' => ['QC Leader', 'Coordinator QC & Commissioning'],
            ],
            'approved_by' => [
                'heading' => 'Approved by / Disetujui oleh:',
                'roles' => ['Unit Kerja'],
            ],
            'known_by' => [
                'heading' => 'Known by / Diketahui Oleh:',
                'roles' => ['Overhaul Management'],
            ],
        ];
    }

    public static function approvalColumns(): array
    {
        $columns = [];

        foreach (self::approvalGroups() as $groupKey => $group) {
            foreach ($group['roles'] as $role) {
                $key = $groupKey.'_'.str($role)->snake()->toString();
                $columns[] = [
                    'key' => $key,
                    'group' => $group['heading'],
                    'role' => $role,
                    'label' => $role,
                ];
            }
        }

        return $columns;
    }

    public static function defaultSchema(string $type): array
    {
        if ($type === self::TYPE_WELDING) {
            return [
                'welder_rows' => [],
                'result_rows' => [
                    ['no' => 1, 'deskripsi' => 'Visual hasil pengelasan', 'keterangan' => ''],
                ],
            ];
        }

        return [
            'rows' => [
                ['item_pengecekan' => '', 'standar' => '', 'actual_default' => '', 'urutan' => 1],
            ],
        ];
    }

    public static function normalizeSchema(?string $type, mixed $schema): array
    {
        $type = self::normalizeType($type);
        $schema = is_array($schema) ? $schema : [];

        if ($type === self::TYPE_WELDING) {
            return [
                'welder_rows' => self::normalizeWelderRows($schema['welder_rows'] ?? []),
                'result_rows' => self::normalizeWeldingResultRows($schema['result_rows'] ?? []),
            ];
        }

        return [
            'rows' => self::normalizeGeneralRows($schema['rows'] ?? []),
        ];
    }

    public static function normalizeType(?string $type): string
    {
        return array_key_exists((string) $type, self::types()) ? (string) $type : self::TYPE_GENERAL;
    }

    public static function templateTypeLabel(?string $type): string
    {
        return self::types()[self::normalizeType($type)] ?? 'QC Umum';
    }

    public static function schemaForTemplate(QcFormTemplate $template): array
    {
        if ($template->template_type) {
            return self::normalizeSchema($template->template_type, $template->body_schema);
        }

        return ['rows' => self::generalRowsFromLegacy($template)];
    }

    public static function generalRowsFromLegacy(QcFormTemplate $template): array
    {
        $rows = [];
        $blocks = $template->relationLoaded('blocks') ? $template->blocks : $template->blocks()->with('tableRows')->get();

        foreach ($blocks as $block) {
            if (! in_array($block->type, ['checklist_table', 'measurement_table'], true)) {
                continue;
            }

            foreach ($block->tableRows as $row) {
                $data = $row->row_data ?? [];
                $rows[] = [
                    'item_pengecekan' => $data['item_pengecekan'] ?? $data['item'] ?? $data['activity'] ?? $data['aktivitas'] ?? $data['parameter'] ?? '',
                    'standar' => $data['standar'] ?? $data['standard'] ?? '',
                    'actual_default' => $data['actual_default'] ?? $data['actual'] ?? $data['aktual'] ?? '',
                    'urutan' => count($rows) + 1,
                ];
            }
        }

        return $rows;
    }

    public static function normalizeGeneralRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row, $index) {
                return [
                    'item_pengecekan' => trim((string) Arr::get($row, 'item_pengecekan', Arr::get($row, 'item', ''))),
                    'standar' => trim((string) Arr::get($row, 'standar', Arr::get($row, 'standard', ''))),
                    'actual_default' => trim((string) Arr::get($row, 'actual_default', Arr::get($row, 'actual', ''))),
                    'urutan' => (int) (Arr::get($row, 'urutan') ?: $index + 1),
                ];
            })
            ->filter(fn ($row) => $row['item_pengecekan'] !== '' || $row['standar'] !== '' || $row['actual_default'] !== '')
            ->sortBy('urutan')
            ->values()
            ->all();
    }

    public static function normalizeWelderRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row, $index) {
                return [
                    'no' => (string) (Arr::get($row, 'no') ?: $index + 1),
                    'nama_welder' => trim((string) Arr::get($row, 'nama_welder', '')),
                    'posisi_pengelasan' => trim((string) Arr::get($row, 'posisi_pengelasan', '')),
                    'diameter_electrode' => trim((string) Arr::get($row, 'diameter_electrode', '')),
                    'electrode_filter' => trim((string) Arr::get($row, 'electrode_filter', '')),
                    'amper' => trim((string) Arr::get($row, 'amper', '')),
                    'keterangan' => trim((string) Arr::get($row, 'keterangan', '')),
                ];
            })
            ->filter(fn ($row) => collect($row)->except('no')->filter()->isNotEmpty())
            ->values()
            ->all();
    }

    public static function normalizeWeldingResultRows(array $rows): array
    {
        return collect($rows)
            ->map(function ($row, $index) {
                return [
                    'no' => (string) (Arr::get($row, 'no') ?: $index + 1),
                    'deskripsi' => trim((string) Arr::get($row, 'deskripsi', Arr::get($row, 'description', ''))),
                    'keterangan' => trim((string) Arr::get($row, 'keterangan', '')),
                ];
            })
            ->filter(fn ($row) => $row['deskripsi'] !== '' || $row['keterangan'] !== '')
            ->values()
            ->all();
    }

    public static function defaultMethods(): array
    {
        return ['Visual Check', 'Uji Destruktif (DT)', 'Penetran Test', 'NDT'];
    }

    public static function defaultCheckSteps(): array
    {
        return ['1', '2', 'Final Check'];
    }
}
