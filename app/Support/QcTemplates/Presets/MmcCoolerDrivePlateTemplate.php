<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;
use App\Support\QcTemplates\TemplateBuilder;

class MmcCoolerDrivePlateTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'MMC Cooler',
            'pekerjaan' => 'Penggantian Drive Plate MMC Cooler',
        ];

        $rows = [
            [
                'no' => 1,
                'aktifitas' => 'Pastikan kondisi drive plate sesuai design',
                'standar' => 'Permukaan Rata (Tickness 25 mm)',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pastikan penguncian drive plate ke Frame sudah sesuai (M24x100 grade 8.8)',
                'standar' => '560 Nm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Pastikan penguncian drive profile ke bearing bracket sudah sesuai (M24x160 grade 8.8)',
                'standar' => '560 Nm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Pastikan penguncian drive profile ke bearing bracket sudah sesuai (M24x180 grade 8.8)',
                'standar' => '560 Nm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Pastikan gap antara c-profile sudah sesuai',
                'standar' => '0.75 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Pastikan penguncian air distribution plate lower sudah sesuai (M16x40 grade 8.8)',
                'standar' => '170 Nm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pastikan penguncian air distribution plate lower/posisi frame sudah sesuai (M16x60 grade 8.8)',
                'standar' => '170 Nm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Pastikan drive plate tidak bergesekan dengan part disamping',
                'standar' => 'Tidak bergesekan',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Pastikan C-Profil tidak dilas dengan Drive Plate',
                'standar' => 'Tidak ada pengelasan',
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
                    'title' => 'Pemeriksaan Drive Plate',
                    'description' => 'Pemeriksaan aktual penggantian Drive Plate MMC Cooler.',
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
            'code' => 'QCR-MMC-DP-001',
            'number' => '04',
            'name' => 'Standard QCR Penggantian MMC Cooler; Drive Plate',
            'category' => 'MMC Cooler',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}