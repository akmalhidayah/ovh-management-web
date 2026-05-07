<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class GrateCoolerInstrumentFieldCrossbarTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Grate Cooler',
            'pekerjaan' => 'Inspeksi Instrument Field Crossbar',
        ];

        $rows = [
            [
                'no' => '',
                'aktifitas' => 'KONDISI STOP / OFFLINE',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi pilot valve',
                'standar' => 'Tidak ada rembesan oli',
                'aktual' => '',
                'keterangan' => 'y58 ada rembesan',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi pilot valve',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi pilot valve',
                'standar' => 'Baut pilot kencang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve proportional',
                'standar' => 'Dudukan proximity kuat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve proportional',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => 'd52m01 lepas',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve proportional',
                'standar' => 'Kebersihan',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve proportional',
                'standar' => 'Indikator LED terlihat',
                'aktual' => '',
                'keterangan' => 'masih off',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve proportional',
                'standar' => 'Indikasi valve sesuai',
                'aktual' => '',
                'keterangan' => 'masih off',
            ],

            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Kebersihan',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Dudukan kencang',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Magnet utuh',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Indikator LED hijau',
                'aktual' => '',
                'keterangan' => 'masih off',
            ],

            [
                'no' => 3,
                'aktifitas' => 'Pengecekan sensor pressure hydraulic',
                'standar' => 'Tidak ada rembesan oli',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Pengecekan sensor pressure hydraulic',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 4,
                'aktifitas' => 'Inspeksi water cooling oil',
                'standar' => 'Tidak ada kebocoran',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Inspeksi water cooling oil',
                'standar' => 'Kabel terkonek',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 5,
                'aktifitas' => 'Inspeksi instrument relief valve',
                'standar' => 'Tidak ada rembesan oli',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Inspeksi instrument relief valve',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Inspeksi instrument relief valve',
                'standar' => 'Baut relief kencang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 6,
                'aktifitas' => 'Inspeksi instrument pressure common header',
                'standar' => 'Tidak ada rembesan oli',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Inspeksi instrument pressure common header',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 7,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve in/out pump',
                'standar' => 'Proximity tidak goyang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve in/out pump',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve in/out pump',
                'standar' => 'Kebersihan',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve in/out pump',
                'standar' => 'Indikator LED terlihat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pengecekan kondisi proximity manual valve in/out pump',
                'standar' => 'Indikasi valve sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 8,
                'aktifitas' => 'Pengecekan indikasi valve filter oil',
                'standar' => 'Proximity tidak goyang',
                'aktual' => '',
                'keterangan' => 'goyang',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Pengecekan indikasi valve filter oil',
                'standar' => 'Posisi valve sesuai',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 9,
                'aktifitas' => 'Cek nilai contamination oli',
                'standar' => 'NAS <18',
                'aktual' => '',
                'keterangan' => 'masih off',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Pengecekan sensor level',
                'standar' => 'Tidak ada alarm',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Sensor pressure bed depth',
                'standar' => 'Indikasi menunjuk',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Sensor pressure bed depth',
                'standar' => 'Tubing tidak bocor/lepas',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Sensor pressure bed depth',
                'standar' => 'Jalur tubing tidak block',
                'aktual' => '',
                'keterangan' => 'belum ready',
            ],

            [
                'no' => '',
                'aktifitas' => 'KONDISI RUNNING / ONLINE',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi pilot valve',
                'standar' => 'Aktual stroke sesuai ±0,5',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pengecekan kondisi pilot valve',
                'standar' => 'Pressure <100 bar',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Posisi mundur full 0 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Posisi maju full 200 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement - Group',
                'standar' => 'Posisi maju 180 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pengecekan sensor displacement',
                'standar' => 'Posisi mundur 15 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pola gerakan',
                'standar' => 'Maju drive 11-22 bersamaan',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pola gerakan',
                'standar' => 'Mundur 11-21 kemudian 12-22',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 3,
                'aktifitas' => 'Pengecekan sensor pressure hydraulic',
                'standar' => 'Tidak ada rembesan oli',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Pengecekan sensor pressure hydraulic',
                'standar' => 'Socket terminasi kencang',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 4,
                'aktifitas' => 'Inspeksi instrument pressure common header',
                'standar' => 'Aktual sesuai setpoint',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 5,
                'aktifitas' => 'Inspeksi motor pump hydraulic',
                'standar' => 'Tiap motor seimbang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Inspeksi motor pump hydraulic',
                'standar' => 'Temperatur winding <90 deg',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Inspeksi motor pump hydraulic',
                'standar' => 'Vibrasi < 4 mm/s',
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
                    'title' => 'Inspeksi Instrument Field Crossbar',
                    'description' => 'Pemeriksaan kondisi stop/offline dan running/online instrument field crossbar Grate Cooler.',
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
            'code' => 'QCR-GC-IFC-001',
            'number' => '23',
            'name' => 'Standard QCR Inspeksi Grate Cooler; Instrument Field Crossbar',
            'category' => 'Grate Cooler',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}