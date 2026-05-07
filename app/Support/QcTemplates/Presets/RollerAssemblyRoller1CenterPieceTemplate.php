<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class RollerAssemblyRoller1CenterPieceTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Roller Assembly',
            'pekerjaan' => 'Penggantian Roller 1 ke Center Piece',
        ];

        $rows = [
            [
                'no' => 1,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => '',
            ],
            [
                'no' => 2,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => '',
            ],
            [
                'no' => 3,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => '',
            ],
            [
                'no' => 4,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => '',
            ],
            [
                'no' => 5,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => '',
            ],
            [
                'no' => 6,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => '',
            ],
        ];

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Drawing',
                'description' => 'Upload foto pekerjaan, drawing pemasangan roller assembly, dan dokumen pendukung bila diperlukan.',
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
                        'key' => 'foto_drawing',
                        'label' => 'Foto Drawing',
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
            'approval' => [
                'title' => 'Approval',
                'columns' => [
                    [
                        'key' => 'tanggal',
                        'label' => 'Tanggal',
                        'type' => 'date',
                    ],
                    [
                        'key' => 'user',
                        'label' => 'User',
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
                    'User: Mekanik RM 5',
                    'Contractor: PT Patriatama Mandiri',
                    'Commisioning: Team OH',
                ],
            ],
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Penguncian Baut Roller 1 to Center Piece',
                    'description' => 'Check Sheet Pemasangan Roller Assembly Roller 1 ke Center Piece.',
                    'columns' => [
                        [
                            'key' => 'no',
                            'label' => 'No',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'tahap_1',
                            'label' => 'Tahap 1 (Bar)',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'tahap_2',
                            'label' => 'Tahap 2 (Bar)',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'tahap_3',
                            'label' => 'Tahap 3 (Bar)',
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
            'code' => 'QCR-RA-R1CP-001',
            'number' => '18',
            'name' => 'Standard QCR Penggantian Roller Assembly; Roller 1 ke Center Piece',
            'category' => 'Roller Assembly',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}