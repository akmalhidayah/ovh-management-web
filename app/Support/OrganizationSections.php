<?php

namespace App\Support;

class OrganizationSections
{
    public static function hierarchy(): array
    {
        return [
            'Clinker & Cement Production' => [
                'Derivative Product & Supporting' => [
                    'Derivative Prod. & Operation 2/3',
                    'Product Contract Compliance & SLA',
                ],
                'Clinker Production' => [
                    'Line 4 RKC Operation',
                    'Kiln Production Coach',
                ],
                'Cement Production' => [
                    'Line 4 Finish Mill Operation',
                    'Line 2/3 FM Operation',
                    'Cement Production Coach',
                    'Line 5 FM Operation',
                ],
            ],
            'Maintenance' => [
                'Reliability Maintenance' => [
                    'PGO',
                ],
                'Elins Maintenance 1' => [
                    'Line 2/3 RKC Elins Maint',
                    'Packer Plant Elins Maint',
                    'Crusher Elins Maintenance',
                    'Line 2/3 FM Elins Maintenance',
                ],
                'Elins Maintenance 2' => [
                    'EP/DC Maintenance',
                    'Line 4/5 RKC Instrument Maint',
                    'Line 4/5 RKC Electrical Maint',
                    'Line 4/5 FM Elins Maint',
                ],
                'Machine Maintenance 1' => [
                    'Line 2/3 FM Machine Maint',
                    'Line 2/3 RKC Machine Maint',
                    'Crusher Machine & Conveyor Maint',
                    'Packer Machine Maintenance',
                ],
                 'Machine Maintenance 2' => [
                    'Line 4/5 FM Machine Maint',
                    'Line 4/5 Kiln & CM Mach Maint',
                    'Line 4/5 RM Machine Maint',
                ],
                'Port Product Discharge Maintenance' => [
                    'Port Facility Elins Maintenance',
                ],
            ],
            'Project Management & Maintenance Support' => [
                'Engineering' => [
                    'Elins Design Engineering',
                    'Civil Design Engineering',
                    'Process Design Engineering',
                ],
                'Workshop' => [
                    'Elins Workshop',
                    'Machine Workshop',
                ],
                'Project Management' => [
                    'Project Execution (Construction)',
                ],
                'CAPEX Management' => [],
                'Maintenance Planning & Evaluation' => [],
            ],
            'Production Planning & Control' => [
                'Quality Control' => [
                    'QC 4/5',
                    'QC 2/3',
                    'Quality Development & Evaluation',
                ],
                'Production Plan Eval & Environmental' => [
                    'Production Planning',
                    'Raw Material & Cement Mill Eval',
                    'RKC Evaluation',
                    'Environmental Monitoring',
                    'PROPER & CDM',
                ],
                'Production Support' => [
                    'Heavy Equipment & Coal Transport',
                    'Plant Hygiene',
                    'Utility',
                ],
                'AFR & Energy' => [
                    'Coal Mixing',
                    'AFR & 3rd Material',
                ],
                'OHS' => [
                    'Plant OHS',
                    'BKS OHS',
                ],
            ],
            'Infrastructure' => [
                'Packing Plant 1' => [
                    'Makassar Packing Plant',
                    'Samarinda Packing Plant',
                    'Balikpapan Packing Plant',
                ],
                'Packing Plant 2' => [
                    'North Maluku Packing Plant',
                    'Ambon Packing Plant',
                    'Bitung Packing Plant',
                    'Kendari Packing Plant',
                    'Sorong Packing Plant',
                    'Mamuju Packing Plant',
                ],
                'SCM Infra Port Management' => [
                    'SCM Infra Port Management',
                ],
                'Plant & Port Product Discharge Operation' => [
                    'Port Operation Packer & Curah',
                    'Port Opr Silo, Aux, T & Silo M',
                    'Plant Site Packer & Bulk Opr',
                ],
                'Interplant Logistic' => [
                    'Plan Eval & Product Distribution',
                    'Sea Interplant',
                    'Land Interplant & DEPO Mgmt',
                ],
            ],
            'Mining & Power Plant' => [
                'Mining Operation' => [
                    'Limestone Mining',
                ],
                'Raw Material Management' => [
                    'Clay Crusher Operation',
                    'Limestone Crusher Operation',
                ],
                'Power Plant Operation' => [
                    'Power Plant Operation',
                    'Continuous System Unloading',
                    'PP Performance & Evaluation',
                    'Water Treatment & CUS Operation',
                ],
                'Power Plant Machine Maintenance' => [
                    'Power Plant Machine Maintenance',
                    'CUS Maintenance',
                ],
                'Power Distribution' => [
                    'Electricity Load Control',
                    'Electrical Network Maintenance',
                ],
                'Power Plant Elins Maintenance' => [
                    'Power Plant Instrument Maintenance',
                    'Power Plant Electrical Maintenance',
                ],
            ],
        ];
    }

    public static function rows(): array
    {
        $rows = [];

        foreach (self::hierarchy() as $department => $units) {
            foreach ($units as $unitKerja => $sections) {
                $sections = $sections === [] ? [$unitKerja] : $sections;

                foreach ($sections as $section) {
                    $rows[] = [
                        'department' => $department,
                        'unit_kerja' => $unitKerja,
                        'section' => $section,
                    ];
                }
            }
        }

        return $rows;
    }
}
