<?php

namespace App\Support\Commissioning;

use Illuminate\Support\Arr;

class FixedCommissioningTemplate
{
    public static function headerFields(): array
    {
        return [
            ['key' => 'doc_number', 'label' => 'Doc.Number', 'type' => 'text'],
            ['key' => 'tahun', 'label' => 'Tahun', 'type' => 'text'],
            ['key' => 'area', 'label' => 'Area', 'type' => 'text'],
            ['key' => 'date_time', 'label' => 'Date & Time', 'type' => 'datetime-local'],
            ['key' => 'tag_num', 'label' => 'Tag.Num', 'type' => 'text'],
            ['key' => 'functional_location', 'label' => 'Functional Location', 'type' => 'text'],
            ['key' => 'name_equipment', 'label' => 'Name Equipment', 'type' => 'text'],
            ['key' => 'id_equipment', 'label' => 'ID Equipment', 'type' => 'text'],
        ];
    }

    public static function approvalColumns(): array
    {
        return [
            ['key' => 'commissioning_leader', 'group' => 'Checked by / Diperiksa Oleh:', 'label' => 'COMMISSIONING Leader'],
            ['key' => 'coordinator_commissioning_qc', 'group' => 'Checked by / Diperiksa Oleh:', 'label' => 'COORDINATOR COMMISSIONING & QC'],
            ['key' => 'unit_kerja', 'group' => 'Approved by / Disetujui oleh:', 'label' => 'UNIT KERJA'],
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
                ['key' => 'power_kw', 'label' => 'Power (kW)'],
                ['key' => 'current_a', 'label' => 'Current (A)'],
                ['key' => 'voltage_v', 'label' => 'Voltage (V)'],
                ['key' => 'freq_hz', 'label' => 'Freq. (Hz)'],
                ['key' => 'brand', 'label' => 'Brand/Merek'],
            ],
            'motor_test_fields' => [
                ['key' => 'starting_current', 'label' => 'Starting Current'],
                ['key' => 'time', 'label' => 'Time'],
                ['key' => 'r', 'label' => 'R'],
                ['key' => 's', 'label' => 'S'],
                ['key' => 't', 'label' => 'T'],
                ['key' => 'horizontal', 'label' => 'Horizontal'],
                ['key' => 'vertical', 'label' => 'Vertical'],
                ['key' => 'axial', 'label' => 'Axial'],
                ['key' => 'remarks', 'label' => 'Remarks'],
            ],
            'motor_test_rows' => self::numberedRows(5),
            'gearbox_rating_fields' => [
                ['key' => 'power_kw', 'label' => 'Power (kW)'],
                ['key' => 'torque_nm', 'label' => 'Torque (Nm)'],
                ['key' => 'brand', 'label' => 'Brand/Merek'],
            ],
            'gearbox_test_fields' => [
                ['key' => 'time', 'label' => 'Time'],
                ['key' => 'temperature', 'label' => 'Temperature'],
                ['key' => 'horizontal', 'label' => 'Horizontal'],
                ['key' => 'vertical', 'label' => 'Vertical'],
                ['key' => 'axial', 'label' => 'Axial'],
                ['key' => 'remarks', 'label' => 'Remarks'],
            ],
            'gearbox_test_rows' => self::numberedRows(5),
            'equipment_check_rows' => [
                ['no' => 1, 'item' => 'Visual inspection equipment'],
                ['no' => 2, 'item' => 'Check installation and mounting'],
                ['no' => 3, 'item' => 'Check lubrication / cleanliness'],
                ['no' => 4, 'item' => 'Check safety and guarding'],
            ],
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
        ];
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
        $labelsByKey = collect($fields)
            ->filter(fn ($field) => Arr::get($field, 'key'))
            ->mapWithKeys(fn ($field) => [Arr::get($field, 'key') => trim((string) Arr::get($field, 'label', ''))]);

        return collect($defaults)
            ->map(fn ($field) => [
                'key' => $field['key'],
                'label' => $labelsByKey->get($field['key']) ?: $field['label'],
            ])
            ->values()
            ->all();
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
