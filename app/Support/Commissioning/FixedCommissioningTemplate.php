<?php

namespace App\Support\Commissioning;

use App\Support\AreaOwnerLabel;
use Illuminate\Support\Arr;

class FixedCommissioningTemplate
{
    public static function headerFields(): array
    {
        return [
            ['key' => 'doc_number', 'label' => 'Doc.Number', 'type' => 'text'],
            ['key' => 'plant', 'label' => 'Plant', 'type' => 'text'],
            ['key' => 'tag_num', 'label' => 'Section No.', 'type' => 'text'],
            ['key' => 'functional_location', 'label' => 'Functional Location', 'type' => 'text'],
            ['key' => 'id_equipment', 'label' => 'ID Equipment', 'type' => 'text'],
            ['key' => 'name_equipment', 'label' => 'Name Equipment', 'type' => 'text'],
            ['key' => 'area', 'label' => 'Area', 'type' => 'text'],
            ['key' => 'date_time', 'label' => 'Date & Time', 'type' => 'datetime-local'],
            ['key' => 'inspector_commissioning', 'label' => 'User Commissioning', 'type' => 'text'],
            ['key' => 'unit_kerja', 'label' => AreaOwnerLabel::fieldLabel(), 'type' => 'text'],
        ];
    }

    public static function approvalColumns(): array
    {
        return [
            ['key' => 'commissioning_leader', 'group' => 'Checked by / Diperiksa Oleh:', 'label' => 'COMMISSIONING Leader'],
            ['key' => 'coordinator_commissioning_qc', 'group' => 'Checked by / Diperiksa Oleh:', 'label' => 'COORDINATOR COMMISSIONING & QC'],
            ['key' => 'unit_kerja', 'group' => 'Approved by / Disetujui oleh:', 'label' => AreaOwnerLabel::fieldLabel()],
            ['key' => 'overhaul_management', 'group' => 'Known by / Diketahui Oleh:', 'label' => 'OVERHAUL MANAGEMENT'],
        ];
    }

    public static function defaultSchema(): array
    {
        return [
            'labels' => [
                'motor_title' => 'Motor Test Report: Load / No Load',
                'gearbox_title' => 'Gearbox Test Report: Load/No Load',
                'equipment_check_title' => 'Equipment Check Data',
                'note_label' => 'Notes/Finding',
                'documentation_label' => 'Dokumentasi: (Wajib)',
            ],
            'motor_rating_fields' => [
                ['key' => 'power_kw', 'label' => 'Power', 'unit' => 'kW'],
                ['key' => 'current_a', 'label' => 'Current', 'unit' => 'A'],
                ['key' => 'voltage_v', 'label' => 'Voltage', 'unit' => 'V'],
                ['key' => 'freq_hz', 'label' => 'Freq.', 'unit' => 'Hz'],
                ['key' => 'brand', 'label' => 'Brand/Merek', 'unit' => null],
            ],
            'motor_test_fields' => [
                ['key' => 'starting_current', 'label' => 'Starting Current', 'unit' => 'A'],
                ['key' => 'time', 'label' => 'Time', 'unit' => 'min'],
                ['key' => 'r', 'label' => 'R', 'unit' => 'A'],
                ['key' => 's', 'label' => 'S', 'unit' => 'A'],
                ['key' => 't', 'label' => 'T', 'unit' => 'A'],
                ['key' => 'horizontal', 'label' => 'Horizontal', 'unit' => 'mm/s'],
                ['key' => 'vertical', 'label' => 'Vertical', 'unit' => 'mm/s'],
                ['key' => 'axial', 'label' => 'Axial', 'unit' => 'mm/s'],
                ['key' => 'remarks', 'label' => 'Remarks', 'unit' => null],
            ],
            'motor_test_rows' => self::numberedRows(5),
            'gearbox_rating_fields' => [
                ['key' => 'power_kw', 'label' => 'Power', 'unit' => 'kW'],
                ['key' => 'torque_nm', 'label' => 'Torque', 'unit' => 'Nm'],
                ['key' => 'brand', 'label' => 'Brand/Merek', 'unit' => null],
            ],
            'gearbox_test_fields' => [
                ['key' => 'time', 'label' => 'Time', 'unit' => 'min'],
                ['key' => 'temperature', 'label' => 'Temperature', 'unit' => 'C'],
                ['key' => 'horizontal', 'label' => 'Horizontal', 'unit' => 'mm/s'],
                ['key' => 'vertical', 'label' => 'Vertical', 'unit' => 'mm/s'],
                ['key' => 'axial', 'label' => 'Axial', 'unit' => 'mm/s'],
                ['key' => 'remarks', 'label' => 'Remarks', 'unit' => null],
            ],
            'gearbox_test_rows' => self::numberedRows(5),
            'equipment_check_rows' => [
                ['no' => 1, 'item' => 'Visual inspection equipment'],
                ['no' => 2, 'item' => 'Check installation and mounting'],
                ['no' => 3, 'item' => 'Check lubrication / cleanliness'],
                ['no' => 4, 'item' => 'Check safety and guarding'],
            ],
            'approval_defaults' => self::defaultApprovalDefaults(),
        ];
    }

    public static function normalizeSchema(mixed $schema): array
    {
        $schema = is_array($schema) ? $schema : [];
        $defaults = self::defaultSchema();

        return [
            'labels' => self::normalizeLabels($schema['labels'] ?? [], $defaults['labels']),
            'motor_rating_fields' => self::normalizeFields($schema['motor_rating_fields'] ?? [], $defaults['motor_rating_fields']),
            'motor_test_fields' => self::normalizeFields($schema['motor_test_fields'] ?? [], $defaults['motor_test_fields']),
            'motor_test_rows' => self::normalizeNumberedRows($schema['motor_test_rows'] ?? [], $defaults['motor_test_rows']),
            'gearbox_rating_fields' => self::normalizeFields($schema['gearbox_rating_fields'] ?? [], $defaults['gearbox_rating_fields']),
            'gearbox_test_fields' => self::normalizeFields($schema['gearbox_test_fields'] ?? [], $defaults['gearbox_test_fields']),
            'gearbox_test_rows' => self::normalizeNumberedRows($schema['gearbox_test_rows'] ?? [], $defaults['gearbox_test_rows']),
            'equipment_check_rows' => self::normalizeEquipmentCheckRows($schema['equipment_check_rows'] ?? []),
            'approval_defaults' => self::normalizeApprovalDefaults($schema['approval_defaults'] ?? []),
        ];
    }

    public static function defaultApprovalDefaults(): array
    {
        return collect(self::approvalColumns())
            ->mapWithKeys(fn ($column) => [$column['key'] => ['name' => '']])
            ->all();
    }

    public static function normalizeApprovalDefaults(array $defaults): array
    {
        return collect(self::approvalColumns())
            ->mapWithKeys(function ($column) use ($defaults) {
                $data = Arr::get($defaults, $column['key'], []);

                return [
                    $column['key'] => [
                        'name' => trim((string) Arr::get($data, 'name', '')),
                    ],
                ];
            })
            ->all();
    }

    public static function numberedRows(int $count): array
    {
        return collect(range(1, $count))
            ->map(fn ($number) => ['no' => (string) $number])
            ->all();
    }

    public static function normalizeLabels(array $labels, array $defaults): array
    {
        return collect($defaults)
            ->mapWithKeys(fn ($default, $key) => [$key => trim((string) ($labels[$key] ?? $default)) ?: $default])
            ->all();
    }

    public static function normalizeFields(array $fields, array $defaults): array
    {
        $fieldsByKey = collect($fields)
            ->filter(fn ($field) => Arr::get($field, 'key'))
            ->keyBy(fn ($field) => Arr::get($field, 'key'));

        return collect($defaults)
            ->map(function ($field) use ($fieldsByKey) {
                $incoming = $fieldsByKey->get($field['key'], []);
                $unit = self::normalizeUnit(Arr::get($incoming, 'unit', $field['unit'] ?? null));
                $label = trim((string) Arr::get($incoming, 'label', '')) ?: $field['label'];

                return [
                    'key' => $field['key'],
                    'label' => self::normalizeFieldLabel($label, $unit),
                    'unit' => $unit,
                ];
            })
            ->values()
            ->all();
    }

    public static function fieldUnitLabel(array $field): ?string
    {
        $unit = self::normalizeUnit($field['unit'] ?? null);

        return $unit ? "({$unit})" : null;
    }

    public static function valueWithUnit(mixed $value, array $field, string $default = ''): string
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return $default;
        }

        $unit = self::normalizeUnit($field['unit'] ?? null);

        if (! $unit || preg_match('/\s*'.preg_quote($unit, '/').'\s*$/i', $text)) {
            return $text;
        }

        return "{$text} {$unit}";
    }

    private static function normalizeUnit(mixed $unit): ?string
    {
        $unit = trim((string) $unit);

        return $unit === '' ? null : $unit;
    }

    private static function normalizeFieldLabel(string $label, ?string $unit): string
    {
        if (! $unit) {
            return $label;
        }

        $quotedUnit = preg_quote($unit, '/');
        $label = preg_replace('/\s*\(\s*'.$quotedUnit.'\s*\)\s*$/i', '', $label) ?: $label;

        return trim($label);
    }

    public static function normalizeNumberedRows(array $rows, array $defaults): array
    {
        $normalized = collect($rows)
            ->map(fn ($row, $index) => [
                'no' => (string) (Arr::get($row, 'no') ?: $index + 1),
            ])
            ->filter(fn ($row) => $row['no'] !== '')
            ->values()
            ->all();

        return $normalized ?: $defaults;
    }

    public static function normalizeEquipmentCheckRows(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row, $index) => [
                'no' => (string) (Arr::get($row, 'no') ?: $index + 1),
                'item' => trim((string) Arr::get($row, 'item', '')),
            ])
            ->filter(fn ($row) => $row['item'] !== '')
            ->values()
            ->all();
    }
}
