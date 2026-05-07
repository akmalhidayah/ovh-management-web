<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class FirebrickTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Rotary Kiln / Firebrick',
            'pekerjaan' => 'Brick Installation',
        ];

        $rows = [
            [
                'kategori' => 'Installation Record / Inspection Check List',
                'item' => 'Packing / Bricks Quality',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Installation Record / Inspection Check List',
                'item' => 'Brick Quality Check',
                'standar' => 'Visual / Sound',
            ],
            [
                'kategori' => 'Installation Record / Inspection Check List',
                'item' => 'Kiln Shell Check',
                'standar' => 'Good / Bad',
            ],
            [
                'kategori' => 'Installation Record / Inspection Check List',
                'item' => 'Marking',
                'standar' => 'Radial / Axial / Tyre / Girth Gear Area',
            ],

            [
                'kategori' => 'Brick Installation',
                'item' => 'Lining Charts',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Installation Mixing Ratio',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => '1st Brick Position',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Joint Radial Brick',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Joint Axial Brick',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Radial Stepping',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Axial Stepping',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Bright Tightenning',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Quality of Closure',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'No. of Shim Plate',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'V Joint at Closure Area',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Joint Old-New Lining',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Brick Installation',
                'item' => 'Crack Brick',
                'standar' => 'OK / NO',
            ],

            [
                'kategori' => 'Cut Brick',
                'item' => 'Avoid MgO Contact W/ Water',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Cut Brick',
                'item' => 'Avoid Cut < 140mm',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Cut Brick',
                'item' => 'Cut Brick Machine',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Cut Brick',
                'item' => 'Cut All Alumina Brick W/ Water',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Cut Brick',
                'item' => 'Cut Straight',
                'standar' => 'OK / NO',
            ],

            [
                'kategori' => 'Tyre / Master Gear Area',
                'item' => 'Full Mortar',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Tyre / Master Gear Area',
                'item' => 'Mortar as per Brick Type',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Tyre / Master Gear Area',
                'item' => 'Mortar Thickness',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Tyre / Master Gear Area',
                'item' => '20 Rings Minimum',
                'standar' => 'OK / NO',
            ],

            [
                'kategori' => 'Final Check',
                'item' => 'Tightening Ring',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Final Check',
                'item' => 'Knocking Every Ring for Checking',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Final Check',
                'item' => 'No of Ring Added Plate',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Final Check',
                'item' => 'Key Bricks Position',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Final Check',
                'item' => 'No of Steel Plate',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Final Check',
                'item' => 'Hot Face Position',
                'standar' => 'OK / NO',
            ],
            [
                'kategori' => 'Final Check',
                'item' => 'Patch Job Old Lining',
                'standar' => 'OK / NO',
            ],
        ];

        return [
            'code' => 'QCR-FB-001',
            'number' => '12',
            'name' => 'Standard QCR Penggantian Firebrick',
            'category' => 'Kiln',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => PresetBlocks::make($meta, $rows, [
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
                            'key' => 'report_by',
                            'label' => 'Report By / QC-SPV',
                            'type' => 'signature',
                        ],
                        [
                            'key' => 'vendor',
                            'label' => 'Vendor',
                            'type' => 'signature_locked',
                        ],
                        [
                            'key' => 'customer_supervisor',
                            'label' => 'Customer Supervisor',
                            'type' => 'signature_locked',
                        ],
                        [
                            'key' => 'approve_by',
                            'label' => 'Approve By',
                            'type' => 'signature_locked',
                        ],
                    ],
                    'notes' => [
                        'Report By: QC / SPV',
                        'Vendor: pihak pelaksana pekerjaan',
                        'Customer Supervisor: pengawas dari customer',
                        'Approve By: pihak yang menyetujui laporan',
                    ],
                ],
            ]),
        ];
    }
}