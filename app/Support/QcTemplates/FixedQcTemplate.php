<?php

namespace App\Support\QcTemplates;

use App\Models\QcFormTemplate;
use App\Support\AreaOwnerLabel;
use Illuminate\Support\Arr;

class FixedQcTemplate
{
    public const TYPE_GENERAL = 'general';
    public const TYPE_WELDING = 'welding';
    public const TYPE_CASTABLE = 'castable';
    public const TYPE_BRICS = 'brics';

    public static function types(): array
    {
        return [
            self::TYPE_GENERAL => 'QC Umum',
            self::TYPE_WELDING => 'QC Welding',
            self::TYPE_CASTABLE => 'QC Instalasi Castable',
            self::TYPE_BRICS => 'QC Instalasi BRICS',
        ];
    }

    public static function headerFields(?string $type = null): array
    {
        if (self::normalizeType($type) === self::TYPE_BRICS) {
            return [
                ['key' => 'doc_number', 'label' => 'Doc.Number', 'type' => 'text'],
                ['key' => 'tahun', 'label' => 'Tahun', 'type' => 'text'],
                ['key' => 'area', 'label' => 'Area', 'type' => 'text'],
                ['key' => 'tag_num', 'label' => 'Tag.Num', 'type' => 'text'],
                ['key' => 'functional_location', 'label' => 'Functional Location', 'type' => 'text'],
                ['key' => 'name_equipment', 'label' => 'Name Equipment', 'type' => 'text'],
                ['key' => 'id_equipment', 'label' => 'ID Equipment', 'type' => 'text'],
            ];
        }

        if (self::normalizeType($type) === self::TYPE_CASTABLE) {
            return [
                ['key' => 'doc_number', 'label' => 'Doc.Number', 'type' => 'text'],
                ['key' => 'plant', 'label' => 'Plant', 'type' => 'text'],
                ['key' => 'area', 'label' => 'Area', 'type' => 'text'],
                ['key' => 'date_time', 'label' => 'Date & Time', 'type' => 'datetime-local'],
                ['key' => 'tag_num', 'label' => 'Section No.', 'type' => 'text'],
                ['key' => 'functional_location', 'label' => 'Functional Loc', 'type' => 'text'],
                ['key' => 'name_equipment', 'label' => 'Name Equipment', 'type' => 'text'],
                ['key' => 'id_equipment', 'label' => 'ID Equipment', 'type' => 'text'],
                ['key' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text'],
                ['key' => 'unit_kerja', 'label' => AreaOwnerLabel::fieldLabel(), 'type' => 'text'],
                ['key' => 'inspector_qc', 'label' => 'Inspector QC', 'type' => 'text'],
                ['key' => 'durasi', 'label' => 'Durasi (Menit)', 'type' => 'text'],
            ];
        }

        return [
            ['key' => 'doc_number', 'label' => 'Doc.Number', 'type' => 'text'],
            ['key' => 'plant', 'label' => 'Plant', 'type' => 'text'],
            ['key' => 'tag_num', 'label' => 'Section', 'type' => 'text'],
            ['key' => 'functional_location', 'label' => 'Functional Location', 'type' => 'text'],
            ['key' => 'id_equipment', 'label' => 'ID Equipment', 'type' => 'text'],
            ['key' => 'name_equipment', 'label' => 'Name Equipment', 'type' => 'text'],
            ['key' => 'area', 'label' => 'Area', 'type' => 'text'],
            ['key' => 'date_time', 'label' => 'Date & Time', 'type' => 'datetime-local'],
            ['key' => 'inspector_qc', 'label' => 'Inspector QC', 'type' => 'text'],
            ['key' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text'],
            ['key' => 'unit_kerja', 'label' => AreaOwnerLabel::fieldLabel(), 'type' => 'text'],
            ['key' => 'durasi', 'label' => 'Durasi (menit)', 'type' => 'text'],
        ];
    }

    public static function headerRows(?string $type = null): array
    {
        if (self::normalizeType($type) === self::TYPE_BRICS) {
            return [
                ['doc_number', 'tahun', 'area'],
                ['tag_num', 'functional_location', 'name_equipment', 'id_equipment'],
            ];
        }

        if (self::normalizeType($type) === self::TYPE_CASTABLE) {
            return [
                ['doc_number', 'plant', 'tag_num'],
                ['functional_location', 'id_equipment', 'name_equipment'],
                ['area', 'date_time', 'inspector_qc'],
                ['pekerjaan', 'unit_kerja', 'durasi'],
            ];
        }

        return [
            ['doc_number', 'plant', 'tag_num'],
            ['functional_location', 'id_equipment', 'name_equipment'],
            ['area', 'date_time', 'inspector_qc'],
            ['pekerjaan', 'unit_kerja', 'durasi'],
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

    public static function approvalColumns(?string $type = null): array
    {
        $type = self::normalizeType($type);

        if ($type === self::TYPE_CASTABLE) {
            return [
                ['key' => 'castable_filled_by', 'group' => 'Approval Castable', 'role' => 'QC Inspektor', 'label' => '*1 diisi'],
                ['key' => 'castable_approved_1', 'group' => 'Approval Castable', 'role' => '*2 disetujui', 'label' => '*2 disetujui'],
                ['key' => 'castable_approved_2', 'group' => 'Approval Castable', 'role' => '*3 disetujui', 'label' => '*3 disetujui'],
            ];
        }

        if ($type === self::TYPE_BRICS) {
            return [
                ['key' => 'brics_report_by', 'group' => 'Report by', 'role' => 'QC Inspektor', 'label' => 'QC / SPV'],
                ['key' => 'brics_vendor', 'group' => 'Vendor', 'role' => 'Vendor', 'label' => 'Vendor'],
                ['key' => 'brics_customer_supervisor', 'group' => 'Customer Supervisor', 'role' => 'Customer Supervisor', 'label' => 'Customer Supervisor'],
                ['key' => 'brics_name_unit', 'group' => 'Name / Unit', 'role' => 'Name / Unit', 'label' => 'Name / Unit'],
                ['key' => 'brics_approve_by', 'group' => 'Approve by', 'role' => 'Approve by', 'label' => 'Approve by'],
            ];
        }

        $columns = [];

        foreach (self::approvalGroups() as $groupKey => $group) {
            foreach ($group['roles'] as $role) {
                $key = $groupKey.'_'.str($role)->snake()->toString();
                $columns[] = [
                    'key' => $key,
                    'group' => $group['heading'],
                    'role' => $role,
                    'label' => $role === 'Unit Kerja' ? AreaOwnerLabel::fieldLabel() : $role,
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
                'approval_defaults' => self::defaultApprovalDefaults($type),
            ];
        }

        if (in_array($type, [self::TYPE_CASTABLE, self::TYPE_BRICS], true)) {
            return [
                'approval_defaults' => self::defaultApprovalDefaults($type),
            ];
        }

        return [
            'rows' => [
                ['item_pengecekan' => '', 'standar' => '', 'urutan' => 1],
            ],
            'approval_defaults' => self::defaultApprovalDefaults($type),
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
                'approval_defaults' => self::normalizeApprovalDefaults($schema['approval_defaults'] ?? [], $type),
            ];
        }

        if (in_array($type, [self::TYPE_CASTABLE, self::TYPE_BRICS], true)) {
            return [
                'approval_defaults' => self::normalizeApprovalDefaults($schema['approval_defaults'] ?? [], $type),
            ];
        }

        return [
            'rows' => self::normalizeGeneralRows($schema['rows'] ?? []),
            'approval_defaults' => self::normalizeApprovalDefaults($schema['approval_defaults'] ?? [], $type),
        ];
    }

    public static function defaultApprovalDefaults(?string $type = null): array
    {
        return collect(self::approvalColumns($type))
            ->mapWithKeys(fn ($column) => [$column['key'] => [
                'name' => '',
                'group' => $column['group'],
                'label' => $column['label'],
            ]])
            ->all();
    }

    public static function normalizeApprovalDefaults(array $defaults, ?string $type = null): array
    {
        $type = self::normalizeType($type);

        return collect(self::approvalColumns($type))
            ->mapWithKeys(function ($column) use ($defaults, $type) {
                $data = Arr::get($defaults, $column['key'], []);
                $groupEditable = self::approvalGroupIsEditable($type, $column['key']);
                $labelEditable = self::approvalLabelIsEditable($type, $column['key']);

                return [
                    $column['key'] => [
                        'name' => trim((string) Arr::get($data, 'name', '')),
                        'group' => $groupEditable ? (trim((string) Arr::get($data, 'group', '')) ?: $column['group']) : $column['group'],
                        'label' => $labelEditable ? (trim((string) Arr::get($data, 'label', '')) ?: $column['label']) : $column['label'],
                    ],
                ];
            })
            ->all();
    }

    public static function approvalColumnsWithDefaults(?string $type = null, array $defaults = [], array $approvalData = [], bool $preserveEditableBlank = false): array
    {
        $type = self::normalizeType($type);

        return collect(self::approvalColumns($type))
            ->map(function (array $column) use ($type, $defaults, $approvalData, $preserveEditableBlank) {
                $key = $column['key'];
                $default = is_array($defaults[$key] ?? null) ? $defaults[$key] : [];
                $approval = is_array($approvalData[$key] ?? null) ? $approvalData[$key] : [];
                $hasApproval = array_key_exists($key, $approvalData);
                $groupEditable = self::approvalGroupIsEditable($type, $key);
                $labelEditable = self::approvalLabelIsEditable($type, $key);

                if ($preserveEditableBlank && $groupEditable && $hasApproval) {
                    $column['group'] = self::approvalEditableValue($type, $key, $approval['group'] ?? '');
                } else {
                    $column['group'] = self::approvalDisplayText(
                        $groupEditable ? ($approval['group'] ?? null) : null,
                        $default['group'] ?? null,
                        $column['group']
                    );
                }

                if ($preserveEditableBlank && $labelEditable && $hasApproval) {
                    $column['label'] = self::approvalEditableValue($type, $key, $approval['label'] ?? '');
                } elseif (
                    in_array($type, [self::TYPE_GENERAL, self::TYPE_WELDING], true)
                    && ($column['role'] ?? null) === 'Unit Kerja'
                    && trim((string) ($approval['label'] ?? '')) !== ''
                ) {
                    $column['label'] = AreaOwnerLabel::approvalLabel($approval['label'], $default['label'] ?? $column['label']);
                } else {
                    $column['label'] = self::approvalDisplayText(
                        $labelEditable ? ($approval['label'] ?? null) : null,
                        $default['label'] ?? null,
                        $column['label']
                    );
                }

                return $column;
            })
            ->all();
    }

    private static function approvalDisplayText(mixed ...$values): string
    {
        foreach ($values as $value) {
            $text = trim((string) $value);

            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    public static function normalizeType(?string $type): string
    {
        return array_key_exists((string) $type, self::types()) ? (string) $type : self::TYPE_GENERAL;
    }

    public static function templateTypeLabel(?string $type): string
    {
        return self::types()[self::normalizeType($type)] ?? 'QC Umum';
    }

    public static function isLockedBodyType(?string $type): bool
    {
        return in_array(self::normalizeType($type), [self::TYPE_CASTABLE, self::TYPE_BRICS], true);
    }

    public static function approvalTitleIsEditable(?string $type, string $key): bool
    {
        return self::approvalGroupIsEditable($type, $key) || self::approvalLabelIsEditable($type, $key);
    }

    public static function approvalGroupIsEditable(?string $type, string $key): bool
    {
        return self::normalizeType($type) === self::TYPE_CASTABLE;
    }

    public static function approvalLabelIsEditable(?string $type, string $key): bool
    {
        $type = self::normalizeType($type);

        return $type === self::TYPE_BRICS;
    }

    public static function approvalEditablePlaceholder(?string $type, string $key): string
    {
        $type = self::normalizeType($type);

        if ($type === self::TYPE_BRICS) {
            return match ($key) {
                'brics_report_by' => 'Contoh: Nama/Jabatan QC',
                'brics_vendor' => 'Contoh: Nama Vendor',
                'brics_customer_supervisor' => 'Contoh: Nama Customer Supervisor',
                'brics_name_unit' => 'Contoh: Nama/Unit',
                'brics_approve_by' => 'Contoh: Nama/Jabatan',
                default => 'Contoh: Nama/Jabatan',
            };
        }

        if ($type === self::TYPE_CASTABLE) {
            return 'Contoh: Nama/Jabatan';
        }

        return 'Nama/Jabatan';
    }

    public static function approvalEditableValue(?string $type, string $key, mixed $value): string
    {
        $value = trim((string) $value);
        $column = collect(self::approvalColumns($type))->firstWhere('key', $key) ?? [];

        if (self::approvalGroupIsEditable($type, $key) && $value === ($column['group'] ?? '')) {
            return '';
        }

        if (self::approvalLabelIsEditable($type, $key) && $value === ($column['label'] ?? '')) {
            return '';
        }

        return $value;
    }

    public static function castableCustomerRows(): array
    {
        return [
            ['key' => 'company', 'no' => '1', 'label' => 'COMPANY', 'hint' => ''],
            ['key' => 'address', 'no' => '2', 'label' => 'ADDRESS', 'hint' => ''],
            ['key' => 'production', 'no' => '3', 'label' => 'PRODUCTION', 'hint' => ''],
            ['key' => 'furnace_type', 'no' => '4', 'label' => 'FURNACE TYPE', 'hint' => ''],
            ['key' => 'management', 'no' => '5', 'label' => 'MANAGEMENT', 'hint' => ''],
            ['key' => 'install_date', 'no' => '6', 'label' => 'INSTALL DATE', 'hint' => ''],
            ['key' => 'install_method', 'no' => '7', 'label' => 'INSTALL METHOD', 'hint' => 'CASTING / GUNNING / TROWELING / RAMMING / ...'],
            ['key' => 'installation_section', 'no' => '8', 'label' => 'INSTALLATION SECTION', 'hint' => 'WALL / ROOF / BOTTOM / CYLINDER / DOOR / STACK / BURNER TILE / SPOUT / ...'],
            ['key' => 'installation_design', 'no' => '9', 'label' => 'INSTALLATION DESIGN', 'hint' => 'VERTICAL / HORIZONTAL / ...'],
            ['key' => 'drawing_no', 'no' => '10', 'label' => 'DRAWING NO', 'hint' => ''],
        ];
    }

    public static function castableInspectionRows(): array
    {
        return [
            ['key' => 'castable_type', 'no' => '1', 'label' => 'CASTABLE TYPE', 'options' => ['Conventional', 'Low Cement'], 'detail_label' => 'Brand'],
            ['key' => 'paddle_mixer', 'no' => '2', 'label' => 'PADDLE MIXER', 'options' => ['Yes', 'No'], 'detail_label' => 'Mixer Capacity'],
            ['key' => 'drinking_water', 'no' => '3', 'label' => 'DRINKING WATER', 'options' => ['Yes', 'No'], 'detail_label' => 'Other Water Quality'],
            ['key' => 'former', 'no' => '4', 'label' => 'FORMER', 'options' => ['Yes', 'No'], 'detail_label' => 'Former Material'],
            ['key' => 'vibro_casting', 'no' => '5', 'label' => 'VIBRO CASTING', 'options' => ['Yes', 'No'], 'detail_label' => 'Vibrator Type'],
            ['key' => 'expantion_joint', 'no' => '6', 'label' => 'EXPANTION JOINT', 'options' => ['Yes', 'No'], 'detail_label' => 'Expantion Distance'],
            ['key' => 'anchor', 'no' => '7', 'label' => 'ANCHOR', 'options' => ['Yes', 'No'], 'detail_label' => 'Anchor Type / Dia'],
            ['key' => 'plastic_cup_for_anchor', 'no' => '8', 'label' => 'PLASTIC CUP FOR ANCHOR', 'options' => ['Yes', 'No']],
            ['key' => 'sample', 'no' => '9', 'label' => 'SAMPLE*', 'options' => ['Yes', 'No']],
            ['key' => 'sample_dimention', 'no' => '10', 'label' => 'SAMPLE DIMENTION', 'unit' => 'mm', 'input' => 'dimension'],
            ['key' => 'water_add', 'no' => '11', 'label' => 'WATER ADD', 'unit' => '% volume', 'input' => 'number'],
            ['key' => 'needle_add', 'no' => '12', 'label' => 'STAINLESS STEEL NEEDLE ADD', 'unit' => '% weight', 'input' => 'number'],
            ['key' => 'mixing_time', 'no' => '13', 'label' => 'MIXING TIME', 'unit' => 'mnt', 'input' => 'number'],
            ['key' => 'thickness', 'no' => '14', 'label' => 'THICKNESS', 'unit' => 'mm', 'input' => 'number'],
            ['key' => 'no_of_layer', 'no' => '15', 'label' => 'NO OF LAYER', 'unit' => 'layer', 'input' => 'number'],
            ['key' => 'no_of_segment', 'no' => '16', 'label' => 'NO OF SEGMENT', 'unit' => 'segment', 'input' => 'number'],
            ['key' => 'segment_area', 'no' => '17', 'label' => 'SEGMENT AREA', 'unit' => 'mm2', 'input' => 'number'],
            ['key' => 'total_installation_time', 'no' => '18', 'label' => 'TOTAL INSTALLATION TIME', 'unit' => 'mnt', 'input' => 'number'],
            ['key' => 'quantity_used', 'no' => '19', 'label' => 'QUANTITY USED', 'unit' => 'Kg', 'input' => 'number'],
        ];
    }

    public static function castableSampleRows(): array
    {
        return [
            ['key' => 'sample_mixing_no', 'label' => 'Sample Mixing no'],
            ['key' => 'batch_number', 'label' => 'Batch Number'],
            ['key' => 'quantity', 'label' => 'Quantity'],
            ['key' => 'qc_name', 'label' => 'QC NAME'],
            ['key' => 'qc_date', 'label' => 'QC DATE'],
        ];
    }

    public static function castableMonitoringColumns(): array
    {
        return [
            ['key' => 'quantity', 'label' => 'Quantity (kg)', 'placeholder' => 'Contoh: 25'],
            ['key' => 'batch_number', 'label' => 'Batch number', 'placeholder' => 'Contoh: B-001'],
            ['key' => 'material_temperature', 'label' => 'Temperatur Material (kering)', 'placeholder' => 'Contoh: 32 C'],
            ['key' => 'room_temperature', 'label' => 'Temperatur Ruangan C', 'placeholder' => 'Contoh: 30 C'],
            ['key' => 'mixing_time', 'label' => 'Waktu Aduk (... Standard...) Menit', 'placeholder' => 'Contoh: 4 menit'],
            ['key' => 'water_percentage', 'label' => 'Persentase (... Standard...) (%)', 'placeholder' => 'Contoh: 6.5%'],
            ['key' => 'water_ph', 'label' => '(... Standard...) PH', 'placeholder' => 'Contoh: 7'],
            ['key' => 'water_temperature', 'label' => 'Temperatur (... Standard...) (C)', 'placeholder' => 'Contoh: 28 C'],
            ['key' => 'installation_location', 'label' => 'Lokasi Pemasangan', 'placeholder' => 'Contoh: Burner area'],
            ['key' => 'remark', 'label' => 'Keterangan', 'placeholder' => 'Catatan monitoring'],
        ];
    }

    public static function defaultCastableMonitoringRows(): array
    {
        return collect(range(1, 5))
            ->map(fn ($number) => collect(self::castableMonitoringColumns())
                ->mapWithKeys(fn ($column) => [$column['key'] => ''])
                ->merge(['no' => (string) $number])
                ->all())
            ->all();
    }

    public static function castableMonitoringSignatures(): array
    {
        return [
            ['key' => 'prepared_by', 'heading' => 'Dibuat Oleh', 'role' => 'Supervisor', 'locked' => false],
            ['key' => 'known_by', 'heading' => 'Mengetahui', 'role' => 'Customer', 'locked' => true],
        ];
    }

    public static function bricsCustomerRows(): array
    {
        return [
            ['key' => 'company_name', 'no' => '1', 'label' => 'COMPANY NAME'],
            ['key' => 'subject', 'no' => '2', 'label' => 'SUBJECT', 'default' => 'BRICK INSTALLATIONS'],
            ['key' => 'locations', 'no' => '3', 'label' => 'LOCATIONS', 'default' => 'ROTARY KILN PLANT'],
            ['key' => 'install_method', 'no' => '4', 'label' => 'INSTALL METHOD', 'default' => 'CLENCH LINING / MORTAR LINING'],
            ['key' => 'installations_section', 'no' => '5', 'label' => 'INSTALLATIONS SECTION', 'default' => 'COOLING ZONE / LTZ / CBZ / UTZ / SAFETY / CALCINING ZONE'],
            ['key' => 'drawing_no', 'no' => '6', 'label' => 'DRAWING No.'],
        ];
    }

    public static function requiredBricsCustomerKeys(): array
    {
        return [
            'company_name',
            'subject',
            'locations',
            'install_method',
            'installations_section',
        ];
    }

    public static function bricsTechnicalRows(): array
    {
        return [
            ['key' => 'kiln_length', 'label' => 'Kiln Length'],
            ['key' => 'starting_metering', 'label' => 'Starting Metering'],
            ['key' => 'kiln_diameter', 'label' => 'Kiln Diameter'],
            ['key' => 'finishing_metering', 'label' => 'Finishing Metering'],
            ['key' => 'activity_date', 'label' => 'Activity Date', 'type' => 'date'],
            ['key' => 'start_finishing_ring', 'label' => 'Start & Finishing Ring'],
        ];
    }

    public static function requiredBricsTechnicalKeys(): array
    {
        return [
            'kiln_length',
            'kiln_diameter',
            'starting_metering',
            'activity_date',
            'finishing_metering',
            'start_finishing_ring',
        ];
    }

    public static function bricsManpowerRows(): array
    {
        return [
            ['left' => 'SPV', 'right' => 'ME'],
            ['left' => 'FOREMAN', 'right' => 'SAFETY'],
            ['left' => 'BRICKLAYER', 'right' => 'QC / QA'],
            ['left' => 'OPERATOR', 'right' => 'HELPER'],
        ];
    }

    public static function bricsInspectionSections(): array
    {
        return [
            ['title' => 'INSTALLATION RECORD / INSPECTION CHECK LIST', 'items' => [
                ['key' => 'packing_bricks_quality', 'no' => '1', 'label' => 'PACKING / BRICKS QUALITY'],
                ['key' => 'brick_quality_check', 'no' => '2', 'label' => 'BRICK QUALITY CHECK'],
                ['key' => 'kiln_shell_check', 'no' => '3', 'label' => 'KILN SHELL CHECK'],
                ['key' => 'marking', 'no' => '4', 'label' => 'MARKING'],
            ]],
            ['title' => 'BRICK INSTALLATION', 'number' => '5', 'items' => [
                ['key' => 'lining_charts', 'no' => '5.1', 'label' => 'LINING CHARTS'],
                ['key' => 'installation_mixing_ratio', 'no' => '5.2', 'label' => 'INSTALLATION MIXING RATIO'],
                ['key' => 'first_brick_position', 'no' => '5.3', 'label' => '1ST BRICK POSITION'],
                ['key' => 'joint_radial_brick', 'no' => '5.4', 'label' => 'JOINT RADIAL BRICK'],
                ['key' => 'joint_axial_brick', 'no' => '5.5', 'label' => 'JOINT AXIAL BRICK'],
                ['key' => 'radial_stepping', 'no' => '5.6', 'label' => 'RADIAL STEPPING'],
                ['key' => 'axial_stepping', 'no' => '5.7', 'label' => 'AXIAL STEPPING'],
                ['key' => 'bright_tightening', 'no' => '6.1', 'label' => 'BRIGHT TIGHTENING'],
                ['key' => 'quality_of_closure', 'no' => '6.2', 'label' => 'QUALITY OF CLOSURE'],
                ['key' => 'no_of_shim_plate', 'no' => '6.3', 'label' => 'NO. OF SHIM PLATE'],
                ['key' => 'v_joint_at_closure_area', 'no' => '7', 'label' => 'V JOINT AT CLOSURE AREA'],
                ['key' => 'joint_old_new_lining', 'no' => '8', 'label' => 'JOINT OLD-NEW LINING'],
                ['key' => 'crack_brick', 'no' => '9', 'label' => 'CRACK BRICK'],
            ]],
            ['title' => 'CUT BRICK', 'number' => '10', 'items' => [
                ['key' => 'avoid_mo_contact_water', 'no' => '10.1', 'label' => 'AVOID Mo CONTACT W/ WATER'],
                ['key' => 'avoid_cut_140mm', 'no' => '10.2', 'label' => 'AVOID CUT < 140MM'],
                ['key' => 'cut_brick_machine', 'no' => '10.3', 'label' => 'CUT BRICK MACHINE'],
                ['key' => 'cut_all_alumina_water', 'no' => '10.4', 'label' => 'CUT ALL ALUMINA BRICK W/ WATER'],
                ['key' => 'cut_straight', 'no' => '10.5', 'label' => 'CUT STRAIGHT'],
            ]],
            ['title' => 'TYRE / MASTER GEAR AREA', 'number' => '11', 'items' => [
                ['key' => 'full_mortar', 'no' => '11.1', 'label' => 'FULL MORTAR'],
                ['key' => 'mortar_as_per_brick_type', 'no' => '11.2', 'label' => 'MORTAR AS PER BRICK TYPE'],
                ['key' => 'mortar_thickness', 'no' => '11.3', 'label' => 'MORTAR THICKNESS'],
                ['key' => 'rings_minimum', 'no' => '11.4', 'label' => '20 RINGS MINIMUM'],
            ]],
            ['title' => 'FINAL CHECK', 'number' => '12', 'items' => [
                ['key' => 'tightening_ring', 'no' => '12.1', 'label' => 'TIGHTENING RING'],
                ['key' => 'knocking_every_ring', 'no' => '12.2', 'label' => 'KNOCKING EVERY RING FOR CHECKING'],
                ['key' => 'no_of_ring_added_plate', 'no' => '12.3', 'label' => 'NO OF RING ADDED PLATE'],
                ['key' => 'key_bricks_position', 'no' => '12.4', 'label' => 'KEY BRICKS POSITION'],
                ['key' => 'no_of_steel_plate', 'no' => '12.5', 'label' => 'NO OF STEEL PLATE'],
                ['key' => 'hot_face_position', 'no' => '12.6', 'label' => 'HOT FACE POSITION'],
                ['key' => 'patch_job_old_lining', 'no' => '12.7', 'label' => 'PATCH JOB OLD LINING'],
            ]],
        ];
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
                    'urutan' => (int) (Arr::get($row, 'urutan') ?: $index + 1),
                ];
            })
            ->filter(fn ($row) => $row['item_pengecekan'] !== '' || $row['standar'] !== '')
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
            ->filter(fn ($row) => trim((string) ($row['no'] ?? '')) !== '' || collect($row)->except('no')->filter()->isNotEmpty())
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
