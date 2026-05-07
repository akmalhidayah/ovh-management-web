<?php

namespace App\Support\QcTemplates\Presets;

use App\Support\QcTemplates\PresetBlocks;

class CrusherRotorHammerSetTemplate
{
    public static function data(): array
    {
        $meta = [
            'equipment' => 'Crusher Rotor',
            'pekerjaan' => 'Penggantian Hammer Set, Grate Basket',
        ];

        return [
            'code' => 'QCR-CR-HSGB-001',
            'number' => '01',
            'name' => 'Standard QCR Penggantian Crusher Rotor; Hammer Set, Grate Basket',
            'category' => 'Crusher',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'meta' => $meta,
            'blocks' => PresetBlocks::make(
                $meta,
                [
                    ['kategori' => 'System Penggerak', 'item' => 'Motor', 'standar' => 'Ampere Normal'],
                    ['kategori' => 'System Penggerak', 'item' => 'Motor', 'standar' => 'Vibrasi Normal'],
                    ['kategori' => 'System Penggerak', 'item' => 'Motor', 'standar' => 'Baut dudukan tidak longgar'],
                    ['kategori' => 'System Penggerak', 'item' => 'V-Belt', 'standar' => 'Tidak retak/ Putus'],
                    ['kategori' => 'Crusher', 'item' => 'Baut Liner', 'standar' => 'Tidak longgar'],
                    ['kategori' => 'Crusher', 'item' => 'Baut Body', 'standar' => 'Tidak longgar'],
                    ['kategori' => 'Crusher', 'item' => 'Liner', 'standar' => 'Tidak Retak'],
                    ['kategori' => 'Crusher', 'item' => 'Hammer', 'standar' => 'Tidak Retak'],
                    ['kategori' => 'Crusher', 'item' => 'Axle Hammer', 'standar' => 'Tidak Bengkok'],
                    ['kategori' => 'Crusher', 'item' => 'Anvil', 'standar' => 'Tidak Retak'],
                    ['kategori' => 'Crusher', 'item' => 'Cover Axle Hammer', 'standar' => 'Baut Pengikat Lengkap'],
                    ['kategori' => 'Crusher', 'item' => 'Grate basket', 'standar' => 'Tidak retak, baut pengikat lengkap'],
                    ['kategori' => 'Bearing Rotor', 'item' => 'Kondisi', 'standar' => 'Tidak ada kelainan bunyi'],
                    ['kategori' => 'Bearing Rotor', 'item' => 'Kondisi', 'standar' => 'Tidak Panas'],
                    ['kategori' => 'Bearing Rotor', 'item' => 'Pelumasan', 'standar' => 'Normal'],
                    ['kategori' => 'Kebersihan', 'item' => 'Main Drive', 'standar' => 'Tidak Berdebu'],
                    ['kategori' => 'Kebersihan', 'item' => 'Dinding Rotor', 'standar' => 'Tidak ada coating'],
                    ['kategori' => 'Kebersihan', 'item' => 'Lantai', 'standar' => 'Tidak Berdebu'],
                    ['kategori' => 'Kebersihan', 'item' => 'Housing Bearing', 'standar' => 'Tidak ada coating'],
                    ['kategori' => 'Sensor', 'item' => 'Speed monitor', 'standar' => 'Berfungsi'],
                    ['kategori' => 'Sensor', 'item' => 'Askew running', 'standar' => 'Berfungsi'],
                    ['kategori' => 'Sensor', 'item' => 'Full Rope', 'standar' => 'Berfungsi'],
                    ['kategori' => 'Lampu Penerangan', 'item' => '', 'standar' => 'Menyala'],
                ],
                [
                    'approval' => [
                        'columns' => [
                            ['key' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
                            ['key' => 'diisi', 'label' => '*1 Diisi', 'type' => 'signature'],
                            ['key' => 'disetujui_1', 'label' => '*2 Disetujui', 'type' => 'signature_locked'],
                            ['key' => 'disetujui_2', 'label' => '*3 Disetujui', 'type' => 'signature_locked'],
                        ],
                        'notes' => [
                            '*1 Supervisor/Inspector pekerjaan',
                            '*2 Manager/atasan supervisor/inspector',
                            '*3 Manager bidang terkait (maint mekanikal/electrical atau production support dll)',
                        ],
                    ],
                    'attachment' => [
                        'title' => 'Lampiran Foto',
                        'description' => 'Upload foto before/after atau dokumen pendukung pemeriksaan.',
                        'fields' => [
                            [
                                'key' => 'foto_before',
                                'label' => 'Foto Before',
                                'type' => 'image',
                                'accept' => 'image/*',
                                'multiple' => true,
                                'required' => false,
                                'max_files' => 5,
                            ],
                            [
                                'key' => 'foto_after',
                                'label' => 'Foto After',
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
                ],
            ),
        ];
    }
}
