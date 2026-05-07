<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class WearingTyreKilnTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Wearing Tyre Kiln',
            'pekerjaan' => 'Penggantian Wearing Tyre Kiln',
        ];

        $rows = [
            [
                'no' => 1,
                'aktifitas' => 'Pastikan dimensi Wearing ring yang dipasang sudah sesuai',
                'standar' => "P.1,P.3 (ID 5750 OD 5890 mm)\nP.2 (ID 5760 OD 5900 mm)",
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pastikan Gap antara Tyre dan Wearing sudah sesuai',
                'standar' => '2 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Pastikan Sambungan wearing ring sudah dikampuh sebelum pengelasan',
                'standar' => 'Kampuh V',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Pastikan Sebelum dan saat pengelasan dalam keadaan bersih',
                'standar' => 'Bersih',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Pastikan Elektroda yang digunakan untuk penyambungan sudah sesuai',
                'standar' => 'E7018/4.0 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Pastikan Pengelasan sambungan Wearing tidak ada keretakan',
                'standar' => 'NDT Weld Test',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pastikan Posisi sambungan dipasang plat stopper',
                'standar' => 'Ada plat Stopper',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Pastikan Elektroda untuk pengelasan plat Stopper sudah sesuai',
                'standar' => 'E7018/4.0 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto',
                'description' => 'Upload foto sebelum dan sesudah pekerjaan, serta dokumen pendukung bila diperlukan.',
                'fields' => [
                    [
                        'key' => 'foto_before',
                        'label' => 'Foto Before',
                        'type' => 'image',
                        'accept' => 'image/*',
                        'multiple' => true,
                        'required' => true,
                        'max_files' => 5,
                    ],
                    [
                        'key' => 'foto_after',
                        'label' => 'Foto After',
                        'type' => 'image',
                        'accept' => 'image/*',
                        'multiple' => true,
                        'required' => true,
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
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Pemeriksaan Wearing Tyre Kiln',
                    'description' => 'Pemeriksaan aktual penggantian Wearing Tyre Kiln.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'aktifitas',
                            'label' => 'Aktifitas',
                            'type' => 'textarea',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'standar',
                            'label' => 'Standar',
                            'type' => 'textarea',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'aktual',
                            'label' => 'Aktual',
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

                break;
            }
        }

        return [
            'code' => 'QCR-WTK-001',
            'number' => '09',
            'name' => 'Standard QCR Penggantian Wearing Tyre Kiln',
            'category' => 'Kiln',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}