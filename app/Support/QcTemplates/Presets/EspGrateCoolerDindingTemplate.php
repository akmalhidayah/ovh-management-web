<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class EspGrateCoolerDindingTemplate
{
    public static function data(): array
    {
        $meta = [
            'unit' => 'ESP Grate Cooler 544EP01',
            'equipment' => 'ESP Grate Cooler',
            'pekerjaan' => 'Replacement Dinding',
        ];

        $rows = [
            [
                'no' => '',
                'aktivitas' => 'Dinding Inlet sisi Timur bagian Selatan',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktivitas' => 'Pastikan Spec. material sesuai order',
                'standar' => 'Baik, sesuai spec *1',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktivitas' => 'Pastikan permukaan dinding rata',
                'standar' => 'Baik, rata tidak bergelombang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktivitas' => 'Pastikan pertemuan antar segmen rata',
                'standar' => 'Baik, rata tidak bergelombang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktivitas' => 'Pastikan penggunaan kawat las sesuai standart',
                'standar' => 'Sesuai spec *2',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktivitas' => 'Pastikan hasil pengelasan rapi',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktivitas' => 'Pastikan hasil pengelasan full',
                'standar' => 'Baik, tidak terputus',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => '',
                'aktivitas' => 'Temuan Abnormalitas',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktivitas' => 'Difuser kondisi tipis dan keropos 3 ea',
                'standar' => 'Baik, tidak terputus',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktivitas' => 'Collecting Plate (CP) bending 16 ea',
                'standar' => 'Baik, lurus dan tebal',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktivitas' => 'Dinding Inlet Screen bagian Utara bocor',
                'standar' => 'Hasil tambalan tertutup, hasil pengelasan full dan rapi',
                'aktual' => '',
                'keterangan' => 'Ware Plat, tebal 10m 2050mm X 1403mm',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Dokumen',
                'description' => 'Upload foto before/after replacement dinding, temuan abnormalitas, dan dokumen pendukung bila diperlukan.',
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
                        'key' => 'foto_abnormalitas',
                        'label' => 'Foto Temuan Abnormalitas',
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
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Replacement Dinding',
                    'description' => 'Pemeriksaan replacement dinding ESP Grate Cooler dan temuan abnormalitas.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No.',
                            'type' => 'text',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'aktivitas',
                            'label' => 'Aktivitas',
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

                array_splice($blocks, $index + 1, 0, [
                    [
                        'type' => 'note',
                        'title' => 'Kendala',
                        'field' => [
                            'name' => 'kendala',
                            'label' => 'Kendala',
                            'type' => 'textarea',
                        ],
                    ],
                    [
                        'type' => 'note',
                        'title' => 'Catatan Spesifikasi',
                        'field' => [
                            'name' => 'catatan_spesifikasi',
                            'label' => 'Catatan Spesifikasi',
                            'type' => 'textarea',
                            'value' => "*1 Spec: Material bahan Ware Plate, Tebal 8 mm; Panjang 11000mm, lebar 4520mm\n*2 Spec: Kawat Las type ESAB7018 (Ware Plate)",
                        ],
                    ],
                ]);

                break;
            }
        }

        return [
            'code' => 'QCR-ESP-GC-D-001',
            'number' => '34',
            'name' => 'Standard QCR Penggantian ESP Grate Cooler; Dinding',
            'category' => 'ESP Grate Cooler',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}