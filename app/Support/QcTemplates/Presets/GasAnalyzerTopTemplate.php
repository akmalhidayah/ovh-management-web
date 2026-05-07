<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class GasAnalyzerTopTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Gas Analyzer',
            'pekerjaan' => 'Inspeksi Gas Analyzer',
        ];

        $rows = [
            [
                'no' => 1,
                'aktifitas' => 'Cek Modul URAS',
                'standar' => 'ON',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Cek Modul MAGNOS',
                'standar' => 'ON',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Cek Pump Micro & Membrane Pump',
                'standar' => 'Tidak vibrasi',
                'aktual' => '',
                'keterangan' => 'Ada 2 pump, cek vibrasi dan membran OK',
            ],

            [
                'no' => 4,
                'aktifitas' => 'Pump Peristaltif',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Roller Pump Peristaltic',
                'standar' => 'Tidak kocak',
                'aktual' => '',
                'keterangan' => '3 roller pump OK',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Tube Peristaltic',
                'standar' => 'Tidak bocor',
                'aktual' => '',
                'keterangan' => 'Tidak ditemukan kebocoran di tube',
            ],

            [
                'no' => 5,
                'aktifitas' => 'Pipe Gas Sampel',
                'standar' => 'Tidak bocor',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Pipe Gas Sampel',
                'standar' => 'Tidak tersumbat',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 6,
                'aktifitas' => 'Probe',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Jalur Probe',
                'standar' => 'Tidak tersumbat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Filter Probe',
                'standar' => 'Bersih',
                'aktual' => '',
                'keterangan' => '',
            ],

            [
                'no' => 7,
                'aktifitas' => 'Cek System Evaporator',
                'standar' => 'Bersih',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Cleaning Hose',
                'standar' => 'Tidak bocor',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Cleaning Hose',
                'standar' => 'Tidak tersumbat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Cek Filter Moisture',
                'standar' => 'Tidak basah',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Cek Sensor Water Flow',
                'standar' => '> 1,5 mws',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Service Circulation Pump',
                'standar' => 'Tidak bocor',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Service Circulation Pump',
                'standar' => 'Tidak vibrasi',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 12,
                'aktifitas' => 'Pembersihan Filter Water Circulation',
                'standar' => 'Tidak tersumbat',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 13,
                'aktifitas' => 'Pelindung Probe panjang 600 kotak 155x170',
                'standar' => 'Terpasang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktifitas' => 'Pastikan Pintu Panel',
                'standar' => 'Tertutup',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 15,
                'aktifitas' => 'Temperatur Ruangan Kabin',
                'standar' => '<25 deg C',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 16,
                'aktifitas' => 'Connection Hose',
                'standar' => 'Kencang',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 16,
                'aktifitas' => 'Connection Hose',
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
                    'title' => 'Inspeksi Gas Analyzer',
                    'description' => 'Pemeriksaan modul, pump, pipe gas sample, probe, evaporator, filter, water flow, dan connection hose Gas Analyzer.',
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
            'code' => 'QCR-GA-TOP-001',
            'number' => '27',
            'name' => 'Standard QCR Inspeksi Gas Analyzer; Top',
            'category' => 'Gas Analyzer',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}