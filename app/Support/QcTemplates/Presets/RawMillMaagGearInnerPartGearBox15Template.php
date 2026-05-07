<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class RawMillMaagGearInnerPartGearBox15Template
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Maag Gear WPU-302',
            'pekerjaan' => 'Pengukuran Backlash dan axial movement bevel pinion gear',
        ];

        $rows = [
            [
                'no' => 1,
                'parameter' => 'Backlash',
                'sudut_0' => '',
                'sudut_90' => '',
                'sudut_180' => '',
                'sudut_270' => '',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'parameter' => 'Std backlash',
                'sudut_0' => '',
                'sudut_90' => '',
                'sudut_180' => '',
                'sudut_270' => '',
                'standar' => '',
                'aktual' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'parameter' => 'Axial movement',
                'sudut_0' => '',
                'sudut_90' => '',
                'sudut_180' => '',
                'sudut_270' => '',
                'standar' => '0.03 - 0.5 mm',
                'aktual' => '',
                'keterangan' => '',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto',
                'description' => 'Upload foto pengukuran backlash, axial movement, dan dokumen pendukung bila diperlukan.',
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
                        'key' => 'diisi',
                        'label' => 'Diperiksa Oleh',
                        'type' => 'signature',
                    ],
                    [
                        'key' => 'disetujui',
                        'label' => 'Disetujui Oleh',
                        'type' => 'signature_locked',
                    ],
                ],
                'notes' => [
                    'Diperiksa oleh QC / inspector pekerjaan',
                    'Disetujui oleh atasan / supervisor terkait',
                ],
            ],
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Pengukuran Backlash dan Axial Movement Bevel Pinion Gear',
                    'description' => 'Checklist Inner Part Gear Box WPU-302 Raw Mill 532 RM 01 - MAAG Gear.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'parameter',
                            'label' => 'Parameter',
                            'type' => 'text',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'sudut_0',
                            'label' => '0°',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'sudut_90',
                            'label' => '90°',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'sudut_180',
                            'label' => '180°',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'sudut_270',
                            'label' => '270°',
                            'type' => 'text',
                            'readonly' => false,
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
            'code' => 'QCR-RM-WPU302-IPGB-015',
            'number' => '15',
            'name' => 'Standard QCR Inspeksi Raw Mill; Maag Gear WPU-302; Inner Part Gear Box',
            'category' => 'Raw Mill',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}