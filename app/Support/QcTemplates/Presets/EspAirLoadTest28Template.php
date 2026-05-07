<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class EspAirLoadTest28Template
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'ESP',
            'pekerjaan' => 'Data Air Load Test',
        ];

        $rows = [];
        $chambers = [
            1 => '533EP02',
            2 => '533EP04',
            3 => '533EP06',
            4 => '533EP08',
        ];

        $settings = [250, 500, 750, 1000, 1300];

        foreach ($chambers as $no => $chamber) {
            foreach ($settings as $index => $setting) {
                $rows[] = [
                    'no' => $index === 0 ? $no : '',
                    'chamber' => $index === 0 ? $chamber : '',
                    'setting_ma' => $setting,
                    'current_ma' => '',
                    'voltage_kv' => '',
                    'current_ac' => '',
                    'voltage_ac' => '',
                    'sparkrate' => '',
                    'pin' => '',
                    'pout' => '',
                    'efisiensi' => '',
                    'ep_fan' => '',
                    'durasi_menit' => '10',
                    'keterangan' => '',
                ];
            }
        }

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Dokumen',
                'description' => 'Upload foto panel, hasil pengukuran, atau dokumen pendukung air load test.',
                'fields' => [
                    [
                        'key' => 'foto_panel',
                        'label' => 'Foto Panel / Chamber',
                        'type' => 'image',
                        'accept' => 'image/*',
                        'multiple' => true,
                        'required' => true,
                        'max_files' => 5,
                    ],
                    [
                        'key' => 'foto_pengukuran',
                        'label' => 'Foto Pengukuran',
                        'type' => 'image',
                        'accept' => 'image/*',
                        'multiple' => true,
                        'required' => false,
                        'max_files' => 5,
                    ],
                    [
                        'key' => 'dokumen_pendukung',
                        'label' => 'Dokumen Pendukung',
                        'type' => 'file',
                        'accept' => '.pdf,.doc,.docx,.xls,.xlsx,image/*',
                        'multiple' => true,
                        'required' => false,
                        'max_files' => 5,
                    ],
                ],
            ],
            'approval' => [
                'title' => 'Approval',
                'columns' => [
                    [
                        'key' => 'menyetujui',
                        'label' => 'Menyetujui',
                        'type' => 'signature_locked',
                    ],
                    [
                        'key' => 'mengetahui',
                        'label' => 'Mengetahui',
                        'type' => 'signature_locked',
                    ],
                    [
                        'key' => 'inspector',
                        'label' => 'Inspector',
                        'type' => 'signature',
                    ],
                ],
                'notes' => [
                    'Menyetujui: Mgr of Line 5 RKC Operation',
                    'Mengetahui: Mgr of EPDC Maintenance',
                    'Inspector: petugas pemeriksa',
                ],
            ],
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'general_info') {
                $blocks[$index] = [
                    'type' => 'general_info',
                    'title' => 'Informasi Umum',
                    'fields' => [
                        [
                            'name' => 'seksi',
                            'label' => 'Seksi',
                            'type' => 'text',
                            'value' => '',
                        ],
                        [
                            'name' => 'area',
                            'label' => 'Area',
                            'type' => 'text',
                            'value' => '',
                        ],
                        [
                            'name' => 'tanggal',
                            'label' => 'Tanggal',
                            'type' => 'date',
                            'value' => '',
                        ],
                        [
                            'name' => 'checked_by',
                            'label' => 'Checked by',
                            'type' => 'text',
                            'value' => '',
                        ],
                    ],
                ];
            }

            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Data Air Load Test',
                    'description' => 'Data pengujian air load test ESP berdasarkan chamber dan setting mA.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'text',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'chamber',
                            'label' => 'Chamber',
                            'type' => 'text',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'setting_ma',
                            'label' => 'Setting (mA)',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'current_ma',
                            'label' => 'Current (mA)',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'voltage_kv',
                            'label' => 'Voltage (kV)',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'current_ac',
                            'label' => 'Current (AC)',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'voltage_ac',
                            'label' => 'Voltage (AC)',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'sparkrate',
                            'label' => 'Sparkrate',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'pin',
                            'label' => 'Pin',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'pout',
                            'label' => 'Pout',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'efisiensi',
                            'label' => 'Efisiensi',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_fan',
                            'label' => 'EP Fan',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'durasi_menit',
                            'label' => 'Durasi (Menit)',
                            'type' => 'number',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'keterangan',
                            'label' => 'Keterangan',
                            'type' => 'textarea',
                            'readonly' => false,
                        ],
                    ],
                    'rows' => $rows,
                ];
            }
        }

        return [
            'code' => 'QCR-ESP-ALT-028',
            'number' => '28',
            'name' => 'Standard QCR Inspeksi ESP; Air Load Test',
            'category' => 'ESP',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}