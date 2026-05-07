<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class EspTeganganTembusInnerPartRawMillTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'ESP Raw Mill 533EP01',
            'pekerjaan' => 'Test Tegangan Tembus Inner Part ESP Raw Mill 533EP01',
        ];

        $rows = [
            [
                'no' => 1,
                'setting_megger' => '1000',
                'ep_02' => '',
                'ep_03' => '',
                'ep_04' => '',
                'ep_05' => '',
                'ep_06' => '',
                'ep_07' => '',
                'ep_08' => '',
                'ep_09' => '',
                'rata_rata' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'setting_megger' => '2500',
                'ep_02' => '',
                'ep_03' => '',
                'ep_04' => '',
                'ep_05' => '',
                'ep_06' => '',
                'ep_07' => '',
                'ep_08' => '',
                'ep_09' => '',
                'rata_rata' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'setting_megger' => '5000',
                'ep_02' => '',
                'ep_03' => '',
                'ep_04' => '',
                'ep_05' => '',
                'ep_06' => '',
                'ep_07' => '',
                'ep_08' => '',
                'ep_09' => '',
                'rata_rata' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'setting_megger' => '10000',
                'ep_02' => '',
                'ep_03' => '',
                'ep_04' => '',
                'ep_05' => '',
                'ep_06' => '',
                'ep_07' => '',
                'ep_08' => '',
                'ep_09' => '',
                'rata_rata' => '',
                'keterangan' => '',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Dokumen',
                'description' => 'Upload foto panel, hasil pengukuran megger, atau dokumen pendukung test tegangan tembus.',
                'fields' => [
                    [
                        'key' => 'foto_panel',
                        'label' => 'Foto Panel / ESP',
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
                    'title' => 'Data Test Tegangan Tembus Inner Part ESP Raw Mill 533EP01',
                    'description' => 'Hasil pengukuran resistansi inner part ESP Raw Mill 533EP01 menggunakan setting megger Volt DC.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'setting_megger',
                            'label' => 'Setting Megger (Volt DC)',
                            'type' => 'text',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'ep_02',
                            'label' => 'EP 02',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_03',
                            'label' => 'EP 03',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_04',
                            'label' => 'EP 04',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_05',
                            'label' => 'EP 05',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_06',
                            'label' => 'EP 06',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_07',
                            'label' => 'EP 07',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_08',
                            'label' => 'EP 08',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'ep_09',
                            'label' => 'EP 09',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'rata_rata',
                            'label' => 'Rata-rata',
                            'type' => 'text',
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
            'code' => 'QCR-ESP-TT-RM533EP01-001',
            'number' => '30',
            'name' => 'Standard QCR Inspeksi ESP; Test Tegangan Tembus Inner Part Raw Mill 533EP01',
            'category' => 'ESP',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}