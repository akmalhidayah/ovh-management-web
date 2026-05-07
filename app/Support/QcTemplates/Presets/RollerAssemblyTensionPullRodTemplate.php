<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class RollerAssemblyTensionPullRodTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Roller Assembly',
            'pekerjaan' => 'Penggantian Tension Pull Rod',
        ];

        $rows = [];

        for ($bolt = 1; $bolt <= 24; $bolt++) {
            $rows[] = [
                'bolt' => $bolt,
                'tahap_1' => '',
                'tahap_2' => '',
                'tahap_3' => '',
                'keterangan' => $bolt === 1
                    ? "Menggunakan Kunci Torsi Merk Tentec\nGap flange after Torsi\n1. 500 Bar = mm\n2. 900 Bar = mm\n3. 13000 Bar = mm\n4.\n5.\n6.\n7.\n8."
                    : '',
            ];
        }

        $blocks = PresetBlocks::make($meta, [], [
            'attachment' => [
                'title' => 'Lampiran Foto / Drawing',
                'description' => 'Upload foto pekerjaan, drawing pemasangan tension pull rod, dan dokumen pendukung bila diperlukan.',
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
                    'title' => 'Pemasangan Tension Pull Rod',
                    'description' => 'Check Sheet Pemasangan Roller Assembly Tension Pull Rod - Roller 1.',
                    'columns' => [
                        [
                            'key' => 'bolt',
                            'label' => 'Bolt',
                            'type' => 'number',
                            'readonly' => true,
                        ],
                        [
                            'key' => 'tahap_1',
                            'label' => 'Tahap 1',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'tahap_2',
                            'label' => 'Tahap 2',
                            'type' => 'text',
                            'readonly' => false,
                        ],
                        [
                            'key' => 'tahap_3',
                            'label' => 'Tahap 3',
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
            'code' => 'QCR-RA-TPR-001',
            'number' => '19',
            'name' => 'Standard QCR Penggantian Roller Assembly; Tension Pull Rod',
            'category' => 'Roller Assembly',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => $blocks,
        ];
    }
}