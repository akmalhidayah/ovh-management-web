<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class KilnFeedInstrumentFieldTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Kiln Feed',
            'pekerjaan' => 'Inspeksi Instrument Field Kiln Feed',
        ];

        $rows = [
            [
                'no' => 1,
                'aktifitas' => 'Inspeksi Load Cell Bin Low',
                'standar' => 'Koneksi kuat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Inspeksi Load Cell Bin Low',
                'standar' => 'Safety LC bebas',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Inspeksi Load Cell Bin Low',
                'standar' => 'Bin tidak terhalang',
                'aktual' => '',
                'keterangan' => 'Masih terganjal (safety)',
            ],

            [
                'no' => 2,
                'aktifitas' => 'Cek Flow Gate KF 02 & BV',
                'standar' => 'Terminasi terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Cek Flow Gate KF 02 & BV',
                'standar' => 'Blade tidak ada benda asing',
                'aktual' => '',
                'keterangan' => 'Terdapat beberapa Bolt & nut saat cleaning',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Cek Flow Gate KF 02 & BV',
                'standar' => 'Fan running',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Cek Flow Gate KF 02 & BV',
                'standar' => 'Temperatur < 35 degC',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Cek Flow Gate KF 02 & BV',
                'standar' => 'Indikasi posisi sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Cek Flow Gate KF 02 & BV',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => 'Ada kebocoran solenoid di Y1',
            ],

            [
                'no' => 3,
                'aktifitas' => 'Cek Flow Gate KF 04 & BV',
                'standar' => 'Terminasi terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Cek Flow Gate KF 04 & BV',
                'standar' => 'Blade tidak ada benda asing',
                'aktual' => '',
                'keterangan' => 'Terdapat beberapa Bolt & nut saat cleaning',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Cek Flow Gate KF 04 & BV',
                'standar' => 'Fan running',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Cek Flow Gate KF 04 & BV',
                'standar' => 'Temperatur < 35 degC',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Cek Flow Gate KF 04 & BV',
                'standar' => 'Indikasi posisi sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Cek Flow Gate KF 04 & BV',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 4,
                'aktifitas' => 'Cek Flow Gate KF 06 & BV',
                'standar' => 'Terminasi terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Cek Flow Gate KF 06 & BV',
                'standar' => 'Blade tidak ada benda asing',
                'aktual' => '',
                'keterangan' => 'Terdapat beberapa Bolt & nut saat cleaning',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Cek Flow Gate KF 06 & BV',
                'standar' => 'Fan running',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Cek Flow Gate KF 06 & BV',
                'standar' => 'Temperatur < 35 degC',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Cek Flow Gate KF 06 & BV',
                'standar' => 'Indikasi posisi sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Cek Flow Gate KF 06 & BV',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 5,
                'aktifitas' => 'Cek Aerasi & Gate Segment A',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Cek Aerasi & Gate Segment A',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => 'Bocor antara actuator ke Flange Valve (S1)',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Cek Aerasi & Gate Segment A',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 6,
                'aktifitas' => 'Cek Aerasi & Gate Segment B',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Cek Aerasi & Gate Segment B',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => 'Bocor di konektor regulator antara valve & regulator',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Cek Aerasi & Gate Segment B',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 7,
                'aktifitas' => 'Cek Aerasi & Gate Segment C',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => 'Open OK Close NOK (S4), O/C bisa tapi keluar udara di Filter Solenoid (S3)',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Cek Aerasi & Gate Segment C',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Cek Aerasi & Gate Segment C',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 8,
                'aktifitas' => 'Cek Aerasi & Gate Segment D',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => 'O/C bisa tapi keluar udara di Filter Solenoid (S3)(S5)',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Cek Aerasi & Gate Segment D',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Cek Aerasi & Gate Segment D',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 9,
                'aktifitas' => 'Cek Aerasi & Gate Segment E',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Cek Aerasi & Gate Segment E',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Cek Aerasi & Gate Segment E',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 10,
                'aktifitas' => 'Cek Aerasi & Gate Segment F',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Cek Aerasi & Gate Segment F',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => 'O/C bisa tapi keluar udara di Filter Solenoid (S1)(S2), konektor input udara solenoid tidak rapat',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Cek Aerasi & Gate Segment F',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 11,
                'aktifitas' => 'Cek Aerasi & Gate Segment G',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Cek Aerasi & Gate Segment G',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Cek Aerasi & Gate Segment G',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => 'Baut konektor coil housingnya rusak',
            ],

            [
                'no' => 12,
                'aktifitas' => 'Cek Gate Pengisian Bin BV01Y01',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 12,
                'aktifitas' => 'Cek Gate Pengisian Bin BV01Y01',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => 'Posisi open keluar udara di Silencer (ada indikasi kebocoran return Valve)',
            ],
            [
                'no' => 12,
                'aktifitas' => 'Cek Gate Pengisian Bin BV01Y01',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 13,
                'aktifitas' => 'Cek Gate Pengisian Bin BV01Y02',
                'standar' => 'Indikasi O/C visual sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 13,
                'aktifitas' => 'Cek Gate Pengisian Bin BV01Y02',
                'standar' => 'Tidak ada kebocoran udara',
                'aktual' => '',
                'keterangan' => 'Posisi close keluar udara di Silencer (ada indikasi kebocoran return Valve)',
            ],
            [
                'no' => 13,
                'aktifitas' => 'Cek Gate Pengisian Bin BV01Y02',
                'standar' => 'Terminasi & coil terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 14,
                'aktifitas' => 'Cek Drive Lenze',
                'standar' => 'LED ready (hijau) menyala',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktifitas' => 'Cek Drive Lenze',
                'standar' => 'LED komunikasi berkedip',
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
                    'title' => 'Inspeksi Instrument Field Kiln Feed',
                    'description' => 'Pemeriksaan instrument field pada area Kiln Feed.',
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
            'code' => 'QCR-KF-IF-001',
            'number' => '24',
            'name' => 'Standard QCR Inspeksi Kiln Feed; Instrument Field',
            'category' => 'Kiln Feed',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}