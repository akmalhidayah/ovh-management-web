<?php

namespace Tests\Feature;

use App\Models\MasterDataRecord;
use App\Models\MasterDataInspectionStatusHistory;
use App\Models\User;
use Database\Seeders\Crusher4MasterDataRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_master_data_page(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-4302-RM-405-BC02',
            'equipment_no' => '20007019',
            'section_no' => '405BC02M1',
            'description' => 'MOTOR BELT CONVEYOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.master-data'))
            ->assertOk()
            ->assertSee('Data Equipment Per Dokumen')
            ->assertSee('MOTOR BELT CONVEYOR')
            ->assertSee('QC')
            ->assertSee('data-filtered-bulk-form', false)
            ->assertSee('QC dan Commissioning');
    }

    public function test_admin_can_create_filter_update_and_delete_master_data(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $payload = [
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-4302-RM-405-BC99',
            'equipment_no' => '90000001',
            'section_no' => '405BC99',
            'description' => 'TEST EQUIPMENT',
            'plant' => 'TONASA 5',
            'area' => 'KILN',
            'status' => 'active',
            'notes' => 'Seed manual test',
        ];

        $this->actingAs($admin)
            ->post(route('admin.master-data.store'), $payload)
            ->assertRedirect(route('admin.master-data', ['document_category' => MasterDataRecord::CATEGORY_COMMISSIONING]));

        $record = MasterDataRecord::where('equipment_no', '90000001')->firstOrFail();
        $this->assertSame($admin->id, $record->created_by);

        $this->actingAs($admin)
            ->get(route('admin.master-data', [
                'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
                'plant' => 'TONASA 5',
                'area' => 'KILN',
                'status' => 'active',
                'search' => 'TEST EQUIPMENT',
            ]))
            ->assertOk()
            ->assertSee('TEST EQUIPMENT');

        $this->actingAs($admin)
            ->put(route('admin.master-data.update', $record), array_merge($payload, [
                'description' => 'UPDATED EQUIPMENT',
                'status' => 'inactive',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('master_data_records', [
            'id' => $record->id,
            'description' => 'UPDATED EQUIPMENT',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.master-data.destroy', $record))
            ->assertRedirect();

        $this->assertDatabaseMissing('master_data_records', [
            'id' => $record->id,
        ]);
    }

    public function test_master_data_seed_is_idempotent(): void
    {
        $this->seed(Crusher4MasterDataRecordSeeder::class);
        $firstCount = MasterDataRecord::count();

        $this->seed(Crusher4MasterDataRecordSeeder::class);

        $this->assertSame($firstCount, MasterDataRecord::count());
        $this->assertDatabaseHas('master_data_records', [
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'equipment_no' => '50003948',
            'description' => 'BELT CONVEYOR',
            'plant' => 'TONASA 4',
            'area' => 'CRUSHER 4',
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'equipment_no' => '50003948',
        ]);
    }

    public function test_admin_can_bulk_activate_and_deactivate_master_data(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $records = collect([
            ['equipment_no' => '91000001', 'status' => 'active'],
            ['equipment_no' => '91000002', 'status' => 'active'],
            ['equipment_no' => '91000003', 'status' => 'inactive'],
        ])->map(fn ($row) => MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-4302-RM-405-BC99-'.$row['equipment_no'],
            'equipment_no' => $row['equipment_no'],
            'section_no' => '405BC99',
            'description' => 'BULK TEST EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => $row['status'],
        ]));

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-status'), [
                'record_ids' => $records->take(2)->pluck('id')->all(),
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '2 master data berhasil diubah menjadi Nonaktif.');

        $this->assertDatabaseHas('master_data_records', ['id' => $records[0]->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('master_data_records', ['id' => $records[1]->id, 'status' => 'inactive']);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-status'), [
                'record_ids' => $records->pluck('id')->all(),
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '3 master data berhasil diubah menjadi Aktif.');

        foreach ($records as $record) {
            $this->assertDatabaseHas('master_data_records', ['id' => $record->id, 'status' => 'active']);
        }
    }

    public function test_admin_can_bulk_update_all_filtered_master_data_across_pages(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        for ($index = 1; $index <= 25; $index++) {
            MasterDataRecord::create([
                'document_category' => MasterDataRecord::CATEGORY_QC,
                'year' => '2026',
                'func_location' => "ST-FILTERED-QC-{$index}",
                'equipment_no' => "FILTERED-QC-{$index}",
                'section_no' => "SEC-QC-{$index}",
                'description' => 'FILTERED RAW MILL EQUIPMENT',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
                'status' => 'active',
            ]);
        }

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-UNFILTERED-QC-1',
            'equipment_no' => 'UNFILTERED-QC-1',
            'section_no' => 'SEC-UNFILTERED-QC-1',
            'description' => 'UNFILTERED KILN EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'KILN',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-filtered-status'), [
                'document_category' => MasterDataRecord::CATEGORY_QC,
                'year' => '2026',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
                'current_status' => 'active',
                'search' => 'FILTERED RAW MILL',
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '25 master data hasil filter berhasil diubah menjadi Nonaktif.');

        $this->assertSame(25, MasterDataRecord::where('area', 'RAW MILL')->where('status', 'inactive')->count());
        $this->assertDatabaseHas('master_data_records', [
            'equipment_no' => 'UNFILTERED-QC-1',
            'status' => 'active',
        ]);
    }

    public function test_admin_inspection_status_update_creates_history_snapshot(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $record = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-MANUAL',
            'equipment_no' => 'EQ-COM-MANUAL',
            'section_no' => 'SEC-COM-MANUAL',
            'description' => 'MANUAL PAPER EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.master-data.inspection-status', $record), [
                'inspection_status' => 'close',
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'close',
                'label' => 'Close',
            ]);

        $this->assertDatabaseHas('master_data_records', [
            'id' => $record->id,
            'inspection_status' => 'close',
        ]);

        $history = MasterDataInspectionStatusHistory::firstOrFail();
        $this->assertSame($record->id, $history->master_data_record_id);
        $this->assertNull($history->previous_status);
        $this->assertSame('close', $history->status);
        $this->assertSame('manual_admin', $history->source);
        $this->assertSame($admin->id, $history->changed_by);
        $this->assertSame('EQ-COM-MANUAL', $history->snapshot['master_data']['equipment_no']);
    }
}
