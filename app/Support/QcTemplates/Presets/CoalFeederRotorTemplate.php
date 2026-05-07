<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class CoalFeederRotorTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Coal Feeder',
            'pekerjaan' => 'Inspeksi Rotor Coal Feeder',
        ];

        $rows = [
            [
                'no' => 1,
                'aktifitas' => 'Body Pfister bebas dari penghalang (scaffolding/mal)',
                'standar' => 'Bebas',
                'aktual' => '',
                'keterangan' => 'Masih ada scaffolding disekitar RK03',
            ],

            [
                'no' => 2,
                'aktifitas' => 'Load Cell Rotor',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Baut pengaman loadcell rotor bebas',
                'standar' => 'Space nut > 5mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Tegangan input loadcell rotor',
                'standar' => '11-13 VDC',
                'aktual' => '',
                'keterangan' => 'No Load 8,25mV dan Load 11,28 mV',
            ],

            [
                'no' => 3,
                'aktifitas' => 'Prehopper',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Baut pengaman loadcell prehopper bebas',
                'standar' => 'Space nut > 5mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Loadcell prehopper tidak bergeser',
                'standar' => '< 2 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Body prehopper',
                'standar' => 'Bebas',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Penutup bin prehopper',
                'standar' => 'Tertutup rapat',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 4,
                'aktifitas' => 'Pengecekan koneksi / terminasi kontrol',
                'standar' => 'Tidak longgar',
                'aktual' => '',
                'keterangan' => 'Konektor tipe jepit',
            ],

            [
                'no' => 5,
                'aktifitas' => 'Encoder',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Kondisi dudukan encoder',
                'standar' => 'Dudukan kencang',
                'aktual' => '',
                'keterangan' => 'Bergetar',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Coupling encoder',
                'standar' => 'Tidak retak',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 6,
                'aktifitas' => 'Proximity rotor',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Sensor proximity rotation rotor',
                'standar' => 'Space 4-6mm',
                'aktual' => '',
                'keterangan' => '5mm',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Pengukuran tegangan proximity rotor',
                'standar' => '10-30VDC',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 7,
                'aktifitas' => 'Aerasi rotor & prehopper',
                'standar' => 'Kabel terkoneksi',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Aerasi rotor & prehopper',
                'standar' => 'Coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Aerasi rotor & prehopper',
                'standar' => 'Pipa tidak bocor',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 8,
                'aktifitas' => 'Check hole pengukuran gap',
                'standar' => 'Tertutup',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Pressure measuring compensator',
                'standar' => 'Bersih',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Pressure service unit',
                'standar' => '>3 BAR',
                'aktual' => '',
                'keterangan' => 'Blower not run',
            ],

            [
                'no' => 11,
                'aktifitas' => 'Cable grounding',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Inlet compensator',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Housing, upper/lower part',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Blow out compensator',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Local panel',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Frame',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 12,
                'aktifitas' => 'Check hole bottom rotor',
                'standar' => 'Tertutup rapat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 13,
                'aktifitas' => 'Baut compensator',
                'standar' => 'Space nut > 5mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktifitas' => 'Kekencangan belt drive',
                'standar' => 'M 7KG < 11 MM',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktifitas' => 'Oil damper',
                'standar' => '',
                'aktual' => '',
                'keterangan' => 'Sebaiknya diganti dengan oil baru',
            ],
            [
                'no' => 15,
                'aktifitas' => 'Cover oil damper',
                'standar' => 'Tertutup',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 16,
                'aktifitas' => 'Level oil damper',
                'standar' => '6-8 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 17,
                'aktifitas' => 'Sensor pressure blower',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 18,
                'aktifitas' => 'Slide gate',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 18,
                'aktifitas' => 'Proximity open/close',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 18,
                'aktifitas' => 'Hose distribution valve',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 18,
                'aktifitas' => 'Hose',
                'standar' => 'Tidak bocor',
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
                    'title' => 'Inspeksi Rotor Coal Feeder',
                    'description' => 'Pemeriksaan rotor Coal Feeder, load cell, prehopper, encoder, proximity, aerasi, dan slide gate.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'text',
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
            'code' => 'QCR-CF-RTR-001',
            'number' => '25',
            'name' => 'Standard QCR Inspeksi Coal Feeder; Rotor',
            'category' => 'Coal Feeder',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}