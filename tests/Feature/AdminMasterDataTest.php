<?php

namespace Tests\Feature;

use App\Models\MasterDataRecord;
use App\Models\User;
use Database\Seeders\MasterDataRecordSeeder;
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
            ->assertSee('QC');
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
        $this->seed(MasterDataRecordSeeder::class);
        $firstCount = MasterDataRecord::count();

        $this->seed(MasterDataRecordSeeder::class);

        $this->assertSame($firstCount, MasterDataRecord::count());
        $this->assertDatabaseHas('master_data_records', [
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'equipment_no' => '20007019',
            'description' => 'MOTOR BELT CONVEYOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'equipment_no' => '20007019',
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
            'func_location' => 'ST-4302-RM-405-BC99',
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
}
