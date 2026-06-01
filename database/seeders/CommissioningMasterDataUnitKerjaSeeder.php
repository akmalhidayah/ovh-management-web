<?php

namespace Database\Seeders;

use App\Models\MasterDataRecord;
use App\Models\OrganizationSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CommissioningMasterDataUnitKerjaSeeder extends Seeder
{
    private const AREA_TO_SECTION = [
        'FINISH MILL 419' => 'Line 4 Finish Mill Operation',
        'FINISH MILL 420' => 'Line 4 Finish Mill Operation',
        'CRUSHER 4' => 'Limestone Crusher Operation',
        'COAL MILL 4' => 'Line 4 RKC Operation',
        'KILN 4' => 'Line 4 RKC Operation',
        'RAW MILL 4' => 'Line 4 RKC Operation',
    ];

    public function run(): void
    {
        if (! Schema::hasTable('master_data_records') || ! Schema::hasColumn('master_data_records', 'organization_section_id')) {
            $this->command?->warn('Kolom master_data_records.organization_section_id belum ada. Jalankan migration dulu.');

            return;
        }

        if (! Schema::hasTable('organization_sections')) {
            $this->command?->warn('Tabel organization_sections belum ada. Jalankan migration dan seed Unit Kerja dulu.');

            return;
        }

        $sections = $this->sectionsByName(array_values(self::AREA_TO_SECTION));
        $totalUpdated = 0;

        foreach (self::AREA_TO_SECTION as $area => $sectionName) {
            $section = $sections[$this->lookupKey($sectionName)] ?? null;

            if (! $section) {
                $this->command?->warn("Unit Kerja '{$sectionName}' tidak ditemukan. Area '{$area}' dilewati.");

                continue;
            }

            $updated = MasterDataRecord::query()
                ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)
                ->where('plant', 'TONASA 4')
                ->whereRaw('LOWER(TRIM(area)) = ?', [$this->lookupKey($area)])
                ->update([
                    'organization_section_id' => $section->id,
                    'updated_at' => now(),
                ]);

            $totalUpdated += $updated;
            $this->command?->info("{$area} => {$sectionName}: {$updated} record diperbarui.");
        }

        $this->command?->info("Selesai. Total {$totalUpdated} record Commissioning diperbarui.");
    }

    /**
     * @param  array<int, string>  $names
     * @return array<string, OrganizationSection>
     */
    private function sectionsByName(array $names): array
    {
        $keys = collect($names)
            ->map(fn (string $name): string => $this->lookupKey($name))
            ->unique()
            ->all();

        return OrganizationSection::query()
            ->get()
            ->filter(fn (OrganizationSection $section): bool => in_array($this->lookupKey($section->section), $keys, true))
            ->keyBy(fn (OrganizationSection $section): string => $this->lookupKey($section->section))
            ->all();
    }

    private function lookupKey(string $value): string
    {
        return strtolower(trim($value));
    }
}
