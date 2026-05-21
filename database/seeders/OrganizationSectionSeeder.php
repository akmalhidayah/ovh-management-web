<?php

namespace Database\Seeders;

use App\Models\OrganizationSection;
use App\Support\OrganizationSections;
use Illuminate\Database\Seeder;

class OrganizationSectionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = collect(OrganizationSections::rows())
            ->map(fn (array $row): array => $row + [
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        OrganizationSection::upsert(
            $rows,
            ['department', 'unit_kerja', 'section'],
            ['status', 'updated_at']
        );
    }
}
