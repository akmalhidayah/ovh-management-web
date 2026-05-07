<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class InnerPartEp02Template
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Inner Part EP02',
            'pekerjaan' => 'Pengecekan Inner Part EP02',
        ];

        $rows = [
            [
                'no' => '',
                'aktifitas' => 'A. Pengecekan Inner Part',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 1,
                'aktifitas' => 'Pastikan collecting plat dimensi sesuai spec, tidak bending, tidak lepas',
                'standar' => 'Baik, sesuai spec *1',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Pastikan discharge electroda dimensi sesuai spec, tidak bengkok, tidak lepas',
                'standar' => 'Baik, sesuai spec *2',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Pastikan jarak CP dengan DE tidak saling berdekatan',
                'standar' => 'Posisi tegak lurus, jarak 200mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Pastikan jumlah CP yang terpasang >208 ea',
                'standar' => 'Kekurangan max. 10% dari kapasitas terpasang *3',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Pastikan jumlah DE yang terpasang >404 ea',
                'standar' => 'Kekurangan max. 10% dari kapasitas terpasang *4',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Pengecekan man hole chamber',
                'standar' => 'Tertutup rapat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Pengecekan dinding dan casing',
                'standar' => 'Tebal 6mm *5',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => '',
                'aktifitas' => 'B. Electrical System',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Pastikan fungsi panel control auto/manual operation',
                'standar' => 'Baik',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Pastikan tidak ada alarm yang muncul',
                'standar' => 'Main Over Load, Under Voltage Alarm, Over Voltage Alarm, Low Temperature, High Temperature, Low Level Oil Alarm, Optional alarm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Pastikan rapping sistem berfungsi merontokkan, tidak tersendat',
                'standar' => 'Berputar, arus beban max. 0.7A',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Pastikan fungsi heater supporting insulator',
                'standar' => 'Panas, setting 70°C *6',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 12,
                'aktifitas' => 'Pastikan fungsi heater rapping insulator',
                'standar' => 'Panas, setting 70°C *6',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => '',
                'aktifitas' => 'C. Pengecekan Trafo',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 13,
                'aktifitas' => 'Pastikan kondisi body trafo bersih',
                'standar' => 'Tidak ada penumpukan debu',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktifitas' => 'Pengecekan kebocoran seal',
                'standar' => 'Tidak ada rembesan oil di bushing ataupun di body trafo',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 15,
                'aktifitas' => 'Pastikan tidak terjadi suara dengungan pada trafo',
                'standar' => 'Tidak ada suara dengungan',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 16,
                'aktifitas' => 'Pastikan temperature body trafo',
                'standar' => 'Tidak melebihi setting 40°C (Temp. rise Max. 50°C)',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 17,
                'aktifitas' => 'Pengecekan lead-in insulator',
                'standar' => 'Kondisi permukaan bersih dan mengikat serta tidak muncul sparking di permukaan',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 18,
                'aktifitas' => 'Air load test',
                'standar' => 'IDC maksimal 1500mA-90kV',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 19,
                'aktifitas' => 'Pengukuran performance',
                'standar' => 'Setting actual 1500mA',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 20,
                'aktifitas' => 'Pastikan nilai spark rendah pada setting 1500mA',
                'standar' => 'Sparkrate <10m/s',
                'aktual' => '',
                'keterangan' => '',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Dokumen',
                'description' => 'Upload foto inner part, electrical system, trafo, dan dokumen pendukung pemeriksaan.',
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
                        'key' => 'foto_inner_part',
                        'label' => 'Foto Inner Part / Trafo',
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
                    'title' => 'Pengecekan Inner Part EP02',
                    'description' => 'Pengecekan inner part, electrical system, dan trafo EP02.',
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
                            'value' => "*1 Spec : tebal >1mm, panjang 14500mm, lebar 487mm\n*2 Spec : Dimensi panjang 6807mm\n*3 Kapasitas terpasang 231 ea\n*4 Kapasitas terpasang 448 ea\n*5 Spec. tebal 6mm\n*6 Range temp. 0 - 200°C",
                        ],
                    ],
                ]);

                break;
            }
        }

        return [
            'code' => 'QCR-IP-EP02-001',
            'number' => '31',
            'name' => 'Standard QCR Inspeksi Inner Part EP02',
            'category' => 'ESP',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}