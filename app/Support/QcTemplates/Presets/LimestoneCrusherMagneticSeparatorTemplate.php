<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class LimestoneCrusherMagneticSeparatorTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Limestone Crusher',
            'pekerjaan' => 'Inspeksi Magnetic Separator',
        ];

        return [
            'code' => 'QCR-LC-MS-001',
            'number' => '22',
            'name' => 'Standard QCR Inspeksi Limestone Crusher; Magnetic Separator',
            'category' => 'Limestone Crusher',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => PresetBlocks::make($meta, []),
        ];
    }
}
