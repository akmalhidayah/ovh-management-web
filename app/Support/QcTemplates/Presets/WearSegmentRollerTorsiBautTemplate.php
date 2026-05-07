<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class WearSegmentRollerTorsiBautTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Wear Segment Roller',
            'pekerjaan' => 'Posisi Clamping dan Torsi Wear Segment Roller',
        ];

        $fixClampingRows = [];
        $flexibleClampingRows = [];

        for ($segment = 1; $segment <= 8; $segment++) {
            $fixClampingRows[] = [
                'segment' => $segment,
                'bolt_1' => '',
                'bolt_2' => '',
                'bolt_3' => '',
                'keterangan' => '',
            ];

            $flexibleClampingRows[] = [
                'segment' => $segment,
                'bolt_1' => '',
                'bolt_2' => '',
                'bolt_3' => '',
                'keterangan' => '',
            ];
        }

        $torsiRows = [];

        for ($bolt = 1; $bolt <= 24; $bolt++) {
            $torsiRows[] = [
                'bolt' => $bolt,
                'torsi' => '',
                'keterangan' => '',
            ];
        }

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Drawing',
                'description' => 'Upload foto posisi clamping, drawing, hasil torsi bolt, dan dokumen pendukung bila diperlukan.',
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
                        'key' => 'owner',
                        'label' => 'Owner',
                        'type' => 'signature',
                    ],
                    [
                        'key' => 'commissioning',
                        'label' => 'Commusioning',
                        'type' => 'signature_locked',
                    ],
                    [
                        'key' => 'contractor',
                        'label' => 'Contractor',
                        'type' => 'signature_locked',
                    ],
                ],
                'notes' => [
                    'Owner: PT. Semen Tonasa',
                    'Contractor: PT. Patriatama Mandiri',
                    'Commusioning: pihak commissioning terkait',
                ],
            ],
        ]);

        foreach ($blocks as $index => $block) {
            if (($block['type'] ?? null) === 'checklist_table') {
                $blocks[$index] = [
                    'type' => 'measurement_table',
                    'title' => 'Posisi Fix Clamping',
                    'description' => 'Posisi Fix Clamping, 0 gap dari hub.',
                    'columns' => [
                        [
                            'key' => 'segment',
                            'label' => 'Segment',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'bolt_1',
                            'label' => 'Bolt 1',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'bolt_2',
                            'label' => 'Bolt 2',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'bolt_3',
                            'label' => 'Bolt 3',
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
                    'rows' => $fixClampingRows,
                ];

                array_splice($blocks, $index + 1, 0, [
                    [
                        'type' => 'measurement_table',
                        'title' => 'Posisi Flexible Clamping',
                        'description' => 'Posisi Flexible Clamping, 0 gap dari hub.',
                        'columns' => [
                            [
                                'key' => 'segment',
                                'label' => 'Segment',
                                'type' => 'number',
                                'readonly' => true,
                            ],
                            [
                                'key' => 'bolt_1',
                                'label' => 'Bolt 1',
                                'type' => 'text',
                                'readonly' => false,
                            ],
                            [
                                'key' => 'bolt_2',
                                'label' => 'Bolt 2',
                                'type' => 'text',
                                'readonly' => false,
                            ],
                            [
                                'key' => 'bolt_3',
                                'label' => 'Bolt 3',
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
                        'rows' => $flexibleClampingRows,
                    ],
                    [
                        'type' => 'measurement_table',
                        'title' => 'Torsi Bolt',
                        'description' => 'Torsi 1300 KN 1000 Bar Tentec atau 1300 Bar Enerpac Roller 1.',
                        'columns' => [
                            [
                                'key' => 'bolt',
                                'label' => 'Bolt',
                                'type' => 'number',
                                'readonly' => true,
                            ],
                            [
                                'key' => 'torsi',
                                'label' => 'Torsi',
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
                        'rows' => $torsiRows,
                    ],
                ]);

                break;
            }
        }

        return [
            'code' => 'QCR-WSR-TB-001',
            'number' => '21',
            'name' => 'Standard QCR Penggantian Wear Segment Roller; Torsi Baut',
            'category' => 'Wear Segment Roller',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}