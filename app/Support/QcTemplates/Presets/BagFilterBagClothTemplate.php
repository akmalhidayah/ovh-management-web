<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class BagFilterBagClothTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Bag Filter',
            'pekerjaan' => 'Penggantian Bag Cloth',
        ];

        $rows = [
            [
                'no' => 1,
                'aktivitas' => 'Pastikan Tube Sheet dimensi sesuai spec, tidak melendut',
                'standar' => 'Baik, sesuai spec *1',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktivitas' => 'Pastikan Bag Cloth dan Cage dimensi sesuai spec, anti static, tidak bocor',
                'standar' => 'Baik, sesuai spec *2',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktivitas' => 'Pastikan Frame Stainless steel, tidak keropos dan lurus',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktivitas' => 'Pastikan Tank Udara blasting tidak bocor',
                'standar' => 'Press > 4Bar',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktivitas' => 'Pastikan Solenoid Valve Energize dan tidak bocor',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktivitas' => 'Pastikan fungsi Adjustable regulator berfungsi dan tidak bocor',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktivitas' => 'Pastikan Blow Pipe + Hose rapat, kuat dan tidak bocor',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktivitas' => 'Pastikan Valve udara Chamber open / close',
                'standar' => 'Full close/open',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktivitas' => 'Pastikan fungsi Panel Control auto/manual operation',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 10,
                'aktivitas' => 'Pastikan fungsi system, sequence dan blasting',
                'standar' => 'Sesuai setting',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktivitas' => 'Pastikan fungsi Heater Bottom Hopper',
                'standar' => 'Panas, setting 70°C *3',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 12,
                'aktivitas' => 'Pengecekan Man Hole Clean Chamber',
                'standar' => 'Tertutup rapat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 13,
                'aktivitas' => 'Pengetesan open/close Safety Door',
                'standar' => 'Mudah dibuka/tutup',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktivitas' => 'Indikasi Limit Safety Door',
                'standar' => 'Indikasi aktual dengan CCR sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 15,
                'aktivitas' => 'Pengecekan dinding dan casing',
                'standar' => 'Tebal 6mm *4, tidak bocor',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 16,
                'aktivitas' => 'Fluorescent Test',
                'standar' => 'Bias sinar tidak tembus ke Clean Chamber',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 17,
                'aktivitas' => 'Pengukuran Differential Pressure (DP)',
                'standar' => '< 15 mBar',
                'aktual' => '',
                'keterangan' => '',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Dokumen',
                'description' => 'Upload foto before/after penggantian bag cloth, kondisi bag filter, dan dokumen pendukung bila diperlukan.',
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
                        'key' => 'foto_bag_filter',
                        'label' => 'Foto Bag Filter / Bag Cloth',
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
                    'title' => 'Penggantian Bag Cloth',
                    'description' => 'Pemeriksaan tube sheet, bag cloth, cage, sistem blasting, safety door, dan differential pressure.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No.',
                            'type' => 'number',
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
                            'value' => "*1 Spec: Tebal 6 mm; Diameter 127mm\n*2 Spec: Diameter 127mm; Panjang 4570mm\n*3 Setting Thermal 70°C (apabila standard berbeda)\n*4 Spec Tebal 6mm (apabila standard berbeda)",
                        ],
                    ],
                ]);

                break;
            }
        }

        return [
            'code' => 'QCR-BF-BC-001',
            'number' => '32',
            'name' => 'Standard QCR Penggantian Bag Filter; Bag Cloth',
            'category' => 'Bag Filter',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}