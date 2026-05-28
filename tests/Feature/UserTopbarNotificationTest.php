<?php

namespace Tests\Feature;

use App\Models\MasterDataRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTopbarNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_qc_user_topbar_shows_active_qc_master_data_notifications(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $record = $this->masterRecord(MasterDataRecord::CATEGORY_QC, 'QC Pump');

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('1 equipment aktif')
            ->assertSee('QC Pump')
            ->assertSee(e(route('user.qc.forms.create', [
                'master_data_record_id' => $record->id,
                'area' => $record->area,
            ])), false);
    }

    public function test_commissioning_user_topbar_shows_active_commissioning_master_data_notifications(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $record = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'COM Motor');

        $this->actingAs($user)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('1 equipment aktif')
            ->assertSee('COM Motor')
            ->assertSee(e(route('user.commissioning.forms.create', [
                'master_data_record_id' => $record->id,
                'area' => $record->area,
            ])), false);
    }

    private function masterRecord(string $category, string $description): MasterDataRecord
    {
        return MasterDataRecord::create([
            'document_category' => $category,
            'year' => '2026',
            'func_location' => 'LOC-'.$description,
            'equipment_no' => 'EQ-'.$description,
            'section_no' => 'SEC-'.$description,
            'description' => $description,
            'plant' => 'TONASA 4',
            'area' => 'KILN',
            'status' => 'active',
        ]);
    }
}
