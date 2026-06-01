<?php

namespace Tests\Feature;

use App\Models\MasterDataRecord;
use App\Models\OrganizationSection;
use Database\Seeders\CommissioningMasterDataUnitKerjaSeeder;
use Database\Seeders\OrganizationSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissioningMasterDataUnitKerjaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_commissioning_master_data_to_unit_kerja_by_area(): void
    {
        $this->seed(OrganizationSectionSeeder::class);

        $finishMill = OrganizationSection::where('section', 'Line 4 Finish Mill Operation')->firstOrFail();
        $limestoneCrusher = OrganizationSection::where('section', 'Limestone Crusher Operation')->firstOrFail();
        $rkc = OrganizationSection::where('section', 'Line 4 RKC Operation')->firstOrFail();

        $finishMill419 = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'FINISH MILL 419');
        $finishMill420 = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'FINISH MILL 420');
        $crusher = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'CRUSHER 4');
        $coalMill = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'COAL MILL 4');
        $kiln = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'KILN 4');
        $rawMill = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'RAW MILL 4');
        $qcRecord = $this->masterRecord(MasterDataRecord::CATEGORY_QC, 'CRUSHER 4');

        $this->seed(CommissioningMasterDataUnitKerjaSeeder::class);

        $this->assertSame($finishMill->id, $finishMill419->fresh()->organization_section_id);
        $this->assertSame($finishMill->id, $finishMill420->fresh()->organization_section_id);
        $this->assertSame($limestoneCrusher->id, $crusher->fresh()->organization_section_id);
        $this->assertSame($rkc->id, $coalMill->fresh()->organization_section_id);
        $this->assertSame($rkc->id, $kiln->fresh()->organization_section_id);
        $this->assertSame($rkc->id, $rawMill->fresh()->organization_section_id);
        $this->assertNull($qcRecord->fresh()->organization_section_id);
    }

    private function masterRecord(string $category, string $area): MasterDataRecord
    {
        return MasterDataRecord::create([
            'document_category' => $category,
            'year' => '2026',
            'func_location' => 'ST-TEST-'.$category.'-'.str_replace(' ', '-', $area),
            'equipment_no' => 'EQ-'.$category.'-'.str_replace(' ', '-', $area),
            'section_no' => 'SEC-'.$category,
            'description' => 'TEST EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => $area,
            'status' => 'active',
        ]);
    }
}
