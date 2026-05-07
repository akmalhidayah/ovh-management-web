<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class RawMillBearingHubRollerNuBearingSupportTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Raw Mill',
            'pekerjaan' => 'Pemasangan Bearing ke Hub Roller NU Bearing dan Spherical Bearing',
        ];

        $rows = [
            [
                'no' => 1,
                'spesifikasi' => 'Inner Bearing NU to shaft',
                'target' => 'Pemanas 120°C',
                'status' => '',
                'note' => '',
            ],
            [
                'no' => 2,
                'spesifikasi' => 'Dry ice Bearing hub',
                'target' => 'Temperatur -4°C',
                'status' => '',
                'note' => '',
            ],
            [
                'no' => 3,
                'spesifikasi' => 'Leveling Hub Roller',
                'target' => '0 mm straight',
                'status' => '',
                'note' => '',
            ],
            [
                'no' => 4,
                'spesifikasi' => 'Leveling shaft',
                'target' => '0 mm straight',
                'status' => '',
                'note' => '',
            ],
            [
                'no' => 5,
                'spesifikasi' => 'Gap outer sleeve bearing to shaft',
                'target' => '0.05-0.1',
                'status' => '',
                'note' => '',
            ],
            [
                'no' => 6,
                'spesifikasi' => 'Pemasangan Seal Oil',
                'target' => 'Position Splite Seal',
                'status' => '',
                'note' => '',
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
                        'key' => 'owner',
                        'label' => 'Owner',
                        'type' => 'signature',
                    ],
                    [
                        'key' => 'contractor',
                        'label' => 'Contractor',
                        'type' => 'signature_locked',
                    ],
                    [
                        'key' => 'commissioning',
                        'label' => 'Commisioning',
                        'type' => 'signature_locked',
                    ],
                ],
                'notes' => [
                    'Owner: Mekanik RM 5',
                    'Contractor: PT Patriatama Mandiri',
                    'Commisioning: Team OH',
                ],
            ],
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Check Sheet Pemasangan Bearing ke Hub Roller NU Bearing dan Spherical Bearing',
                    'description' => 'Pemeriksaan pemasangan bearing ke hub roller NU Bearing dan Spherical Bearing.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'spesifikasi',
                            'label' => 'Spesifikasi',
                            'type' => 'textarea',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'target',
                            'label' => 'Target',
                            'type' => 'textarea',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'status',
                            'label' => 'Status',
                            'type' => 'select',
                            'readonly' => false,
                            'options' => [
                                'OK',
                                'Not OK',
                            ],
                        ],
                        [
                            'key' => 'note',
                            'label' => 'Note',
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
            'code' => 'QCR-RM-BHRNBS-001',
            'number' => '17',
            'name' => 'Standard QCR Penggantian Raw Mill; Bearing ke Hub Roller NU Bearing & Support',
            'category' => 'Raw Mill',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}