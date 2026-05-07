<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class BeltConveyorTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Belt Conveyor',
            'pekerjaan' => 'Penggantian Belt Conveyor',
        ];

        $rows = [
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Idler Roller',
                'standar' => 'Berputar dengan baik',
            ],
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Cleaning Roller',
                'standar' => 'Berputar dengan baik',
            ],
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Cleaning Roller',
                'standar' => 'Disc rubber tidak aus',
                'catatan' => 'Rubber roller aus',
            ],
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Impact Roller',
                'standar' => 'Berputar dengan baik',
            ],
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Impact Roller',
                'standar' => 'Disc rubber tidak aus',
            ],
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Guide Roller',
                'standar' => 'Berfungsi',
            ],
            [
                'kategori' => 'Roller-Roller',
                'item' => 'Frame Roller',
                'standar' => 'Tidak retak',
            ],

            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Sambungan Belt',
                'standar' => 'Tidak Terbuka/ Lepas',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Rubber',
                'standar' => 'Tidak Robek',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Rubber',
                'standar' => 'Tidak bocor',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Drive Belt (Gearbox)',
                'standar' => 'Level Oli Normal',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Drive Belt (Gearbox)',
                'standar' => 'Baut dudukan tidak longgar',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Motor',
                'standar' => 'Ampere Normal',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Motor',
                'standar' => 'Vibrasi Normal',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Motor',
                'standar' => 'Baut dudukan tidak longgar',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Fluid Coupling',
                'standar' => 'Tidak ada kebocoran Oli',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Drum Drive',
                'standar' => 'Tidak retak',
            ],
            [
                'kategori' => 'Belt Conveyor',
                'item' => 'Drum Drive',
                'standar' => 'Rubber lagging tdk aus',
            ],

            [
                'kategori' => 'Bearing',
                'item' => 'Kondisi',
                'standar' => 'Terpasang',
            ],
            [
                'kategori' => 'Bearing',
                'item' => 'Kondisi',
                'standar' => 'Tidak Kotor',
            ],
            [
                'kategori' => 'Bearing',
                'item' => 'Pelumasan',
                'standar' => 'Normal',
            ],

            [
                'kategori' => 'Kebersihan',
                'item' => 'Motor',
                'standar' => 'Tidak ada debu',
            ],
            [
                'kategori' => 'Kebersihan',
                'item' => 'Gearbox',
                'standar' => 'Tidak ada coating',
            ],
            [
                'kategori' => 'Kebersihan',
                'item' => 'Lantai',
                'standar' => 'Tidak ada coating',
            ],
            [
                'kategori' => 'Kebersihan',
                'item' => 'Chute',
                'standar' => 'Tidak ada coating',
            ],

            [
                'kategori' => 'Sensor',
                'item' => 'Speed monitor',
                'standar' => 'Berfungsi',
            ],
            [
                'kategori' => 'Sensor',
                'item' => 'Askew running',
                'standar' => 'Berfungsi',
            ],
            [
                'kategori' => 'Sensor',
                'item' => 'Full Rope',
                'standar' => 'Berfungsi',
            ],

            [
                'kategori' => 'Lampu Penerangan',
                'item' => '',
                'standar' => 'Menyala',
            ],
        ];

        return [
            'code' => 'QCR-BC-001',
            'number' => '02',
            'name' => 'Standard QCR Penggantian Belt Conveyor',
            'category' => 'Belt Conveyor',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => PresetBlocks::make($meta, $rows, [
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
                'approval' => [
                    'columns' => [
                        [
                            'key' => 'tanggal',
                            'label' => 'Tanggal',
                            'type' => 'date',
                        ],
                        [
                            'key' => 'diisi',
                            'label' => '*1 Diisi',
                            'type' => 'signature',
                        ],
                        [
                            'key' => 'disetujui_1',
                            'label' => '*2 Disetujui',
                            'type' => 'signature_locked',
                        ],
                        [
                            'key' => 'disetujui_2',
                            'label' => '*3 Disetujui',
                            'type' => 'signature_locked',
                        ],
                    ],
                    'notes' => [
                        '*1 Supervisor/Inspector pekerjaan',
                        '*2 Manager/atasan supervisor/inspector',
                        '*3 Manager bidang terkait (maint mekanikal/electrical atau production support dll)',
                    ],
                ],
            ]),
        ];
    }
}