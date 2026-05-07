<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class WearSegmentRollerHubRollerTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Wear Segment Roller',
            'pekerjaan' => 'Pemasangan Wear Segment Roller Raw Mill 5',
        ];

        $rows = [];

        for ($segment = 1; $segment <= 8; $segment++) {
            $rows[] = [
                'segment' => $segment,
                'position_1' => '',
                'position_2' => '',
                'position_3' => '',
                'position_4' => '',
                'position_5' => '',
                'position_6' => '',
                'position_7' => '',
                'position_8' => '',
                'position_9' => '',
                'position_10' => '',
                'keterangan' => '',
            ];
        }

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Drawing',
                'description' => 'Upload foto pemasangan wear segment, drawing posisi, dan dokumen pendukung bila diperlukan.',
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
                    'Contractor: PT Patriatama',
                    'Commisioning: Team OH',
                ],
            ],
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Posisi Wear Segment Terhadap Hub Roller',
                    'description' => 'Check Sheet Pemasangan Wear Segment Roller Raw Mill 5. Maximum gap 0,2 mm sebelum ditorsi.',
                    'columns' => [
                        [
                            'key' => 'segment',
                            'label' => 'Segment',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'position_1',
                            'label' => 'Position 1',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_2',
                            'label' => 'Position 2',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_3',
                            'label' => 'Position 3',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_4',
                            'label' => 'Position 4',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_5',
                            'label' => 'Position 5',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_6',
                            'label' => 'Position 6',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_7',
                            'label' => 'Position 7',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_8',
                            'label' => 'Position 8',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_9',
                            'label' => 'Position 9',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'position_10',
                            'label' => 'Position 10',
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
            'code' => 'QCR-WSR-HR-001',
            'number' => '20',
            'name' => 'Standard QCR Penggantian Wear Segment Roller; Hub Roller',
            'category' => 'Wear Segment Roller',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}