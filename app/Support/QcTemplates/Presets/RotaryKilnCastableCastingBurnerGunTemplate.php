<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class RotaryKilnCastableCastingBurnerGunTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Paddle Mixer',
            'pekerjaan' => 'Instalasi Castable',
        ];

        $customerRows = [
            [
                'no' => 1,
                'data' => 'Company',
                'value' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'data' => 'Address',
                'value' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'data' => 'Production',
                'value' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'data' => 'Furnace Type',
                'value' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'data' => 'Management',
                'value' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'data' => 'Install Date',
                'value' => '',
                'keterangan' => '',
            ],
            [
                'no' => 7,
                'data' => 'Install Method',
                'value' => '',
                'keterangan' => 'CASTING / GUNNING / TROWELING / RAMMING / ...',
            ],
            [
                'no' => 8,
                'data' => 'Installation Section',
                'value' => '',
                'keterangan' => 'WALL / ROOF / BOTTOM / CYLINDER / DOOR / STACK / BURNER TILE / SPOUT / ...',
            ],
            [
                'no' => 9,
                'data' => 'Installation Design',
                'value' => '',
                'keterangan' => 'VERTICAL / HORIZONTAL / ...',
            ],
            [
                'no' => 10,
                'data' => 'Drawing No',
                'value' => '',
                'keterangan' => '',
            ],
        ];

        $inspectionRows = [
            [
                'no' => 1,
                'aktifitas' => 'Castable Type',
                'standar' => 'Conventional / Low Cement',
                'aktual' => '',
                'keterangan' => 'Brand',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Paddle Mixer',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => 'Mixer Capacity',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Drinking Water',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => 'Other Water Quality',
            ],
            [
                'no' => 4,
                'aktifitas' => 'Former',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => 'Former Material',
            ],
            [
                'no' => 5,
                'aktifitas' => 'Vibro Casting',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => 'Vibrator Type',
            ],
            [
                'no' => 6,
                'aktifitas' => 'Expansion Joint',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => 'Expansion Distance',
            ],
            [
                'no' => 7,
                'aktifitas' => 'Anchor',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => 'Anchor Type / Dia',
            ],
            [
                'no' => 8,
                'aktifitas' => 'Plastic Cup for Anchor',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 9,
                'aktifitas' => 'Sample*',
                'standar' => 'Yes / No',
                'aktual' => '',
                'keterangan' => '*Sample For Laboratory test by QC',
            ],
            [
                'no' => 10,
                'aktifitas' => 'Sample Dimention',
                'standar' => '( x x ) mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 11,
                'aktifitas' => 'Water Add',
                'standar' => '% volume',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 12,
                'aktifitas' => 'Stainless Steel Needle Add',
                'standar' => '% weight',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 13,
                'aktifitas' => 'Mixing Time',
                'standar' => 'mnt',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 14,
                'aktifitas' => 'Thickness',
                'standar' => 'mm',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 15,
                'aktifitas' => 'No of Layer',
                'standar' => 'layer',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 16,
                'aktifitas' => 'No of Segment',
                'standar' => 'segment',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 17,
                'aktifitas' => 'Segment Area',
                'standar' => 'mm²',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 18,
                'aktifitas' => 'Total Installation Time',
                'standar' => 'mnt',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 19,
                'aktifitas' => 'Quantity Used',
                'standar' => 'Kg',
                'aktual' => '',
                'keterangan' => '',
            ],
        ];

        $sampleRows = [
            [
                'no' => 1,
                'aktifitas' => 'Sample Mixing No',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'aktifitas' => 'Batch Number',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'aktifitas' => 'Quantity',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'aktifitas' => 'QC Name',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'aktifitas' => 'QC Sign / Date',
                'standar' => '',
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
            'approval' => [
                'title' => 'Approval',
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
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Customer Data',
                    'description' => 'Data customer dan informasi instalasi castable.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'data',
                            'label' => 'Data',
                            'type' => 'text',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'value',
                            'label' => 'Isian',
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
                    'rows' => $customerRows,
                ];

                array_splice($blocks, $index + 1, 0, [
                    [
                        'type' => 'measurement_table',
                        'title' => 'Installation Record / Inspection Check List',
                        'description' => 'Checklist instalasi castable.',
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
                        'rows' => $inspectionRows,
                    ],
                    [
                        'type' => 'measurement_table',
                        'title' => 'Sample Data',
                        'description' => 'Data sample untuk laboratory test by QC.',
                        'columns' => [
                            [
                                'key' => 'no',
                                'label' => 'No',
                                'type' => 'number',
                                'readonly' => true,
                            ],
                            [
                                'key' => 'aktifitas',
                                'label' => 'Sample Data',
                                'type' => 'textarea',
                                'readonly' => true,
                            ],
                            [
                                'key' => 'aktual',
                                'label' => 'Isian',
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
                        'rows' => $sampleRows,
                    ],
                ]);

                break;
            }
        }

        return [
            'code' => 'QCR-RK-CCBG-001',
            'number' => '13',
            'name' => 'Standard QCR Penggantian Rotary Kiln; Castable Casting Burner gun',
            'category' => 'Rotary Kiln',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}