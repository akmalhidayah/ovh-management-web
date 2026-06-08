<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataInspectionStatusHistory;
use App\Models\MasterDataRecord;
use App\Models\MasterDataStatusHistory;
use App\Models\OrganizationSection;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
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
            ->assertSee('Data Equipment')
            ->assertSee('MOTOR BELT CONVEYOR')
            ->assertSee('QC')
            ->assertSee('Aktif QC')
            ->assertSee('Aktif Commissioning')
            ->assertSee('data-filtered-bulk-form', false)
            ->assertSee('QC dan Commissioning');
    }

    public function test_master_data_page_shows_active_records_first(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-A-INACTIVE',
            'equipment_no' => 'EQ-A-INACTIVE',
            'section_no' => 'SEC-A-INACTIVE',
            'description' => 'FIRST SORTED BUT INACTIVE',
            'plant' => 'TONASA 4',
            'area' => 'COAL MILL',
            'status' => 'inactive',
        ]);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-Z-ACTIVE',
            'equipment_no' => 'EQ-Z-ACTIVE',
            'section_no' => 'SEC-Z-ACTIVE',
            'description' => 'LAST SORTED BUT ACTIVE',
            'plant' => 'TONASA 5',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.master-data'))
            ->assertOk()
            ->assertSeeInOrder([
                'LAST SORTED BUT ACTIVE',
                'FIRST SORTED BUT INACTIVE',
            ]);
    }

    public function test_admin_can_create_filter_update_and_delete_master_data(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $unitKerja = OrganizationSection::create([
            'department' => 'MAINTENANCE',
            'unit_kerja' => 'OVERHAUL',
            'section' => 'Line 2/3 FM Operation',
            'status' => 'active',
        ]);

        $payload = [
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-4302-RM-405-BC99',
            'equipment_no' => '90000001',
            'section_no' => '405BC99',
            'description' => 'TEST EQUIPMENT',
            'plant' => 'TONASA 5',
            'area' => 'KILN',
            'organization_section_id' => $unitKerja->id,
            'status' => 'active',
            'notes' => 'Seed manual test',
        ];

        $this->actingAs($admin)
            ->post(route('admin.master-data.store'), $payload)
            ->assertRedirect(route('admin.master-data', ['document_category' => MasterDataRecord::CATEGORY_COMMISSIONING]));

        $record = MasterDataRecord::where('equipment_no', '90000001')->firstOrFail();
        $this->assertSame($admin->id, $record->created_by);
        $this->assertSame($unitKerja->id, $record->organization_section_id);

        $this->actingAs($admin)
            ->get(route('admin.master-data', [
                'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
                'plant' => 'TONASA 5',
                'area' => 'KILN',
                'status' => 'active',
                'search' => 'TEST EQUIPMENT',
            ]))
            ->assertOk()
            ->assertSee('TEST EQUIPMENT')
            ->assertSee('Unit Kerja: Line 2/3 FM Operation');

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

    public function test_bulk_deactivation_skips_qc_equipment_used_by_active_draft_and_records_history(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = QcFormTemplate::create([
            'code' => 'QC-GUARD-001',
            'name' => 'QC Guard Template',
            'category' => 'QC',
            'status' => 'active',
        ]);
        $protectedRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-QC-GUARD-PROTECTED',
            'equipment_no' => 'EQ-QC-GUARD-PROTECTED',
            'description' => 'QC PROTECTED EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);
        $eligibleRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-QC-GUARD-ELIGIBLE',
            'equipment_no' => 'EQ-QC-GUARD-ELIGIBLE',
            'description' => 'QC ELIGIBLE EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $admin->id,
            'form_number' => '001/QC/06-2026',
            'status' => 'draft',
            'general_info' => [
                'master_data_record_id' => $protectedRecord->id,
                'functional_location' => $protectedRecord->func_location,
                'id_equipment' => $protectedRecord->equipment_no,
            ],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-status'), [
                'record_ids' => [$protectedRecord->id, $eligibleRecord->id],
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas(
                'success',
                '1 master data berhasil diubah menjadi Nonaktif. 1 dilewati karena masih digunakan oleh draft/submission aktif.'
            );

        $this->assertDatabaseHas('master_data_records', [
            'id' => $protectedRecord->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'id' => $eligibleRecord->id,
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('master_data_status_histories', [
            'master_data_record_id' => $eligibleRecord->id,
            'previous_status' => 'active',
            'status' => 'inactive',
            'source' => 'bulk_selected',
            'changed_by' => $admin->id,
        ]);
        $this->assertDatabaseMissing('master_data_status_histories', [
            'master_data_record_id' => $protectedRecord->id,
        ]);
    }

    public function test_single_update_cannot_deactivate_equipment_used_by_active_submission(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = QcFormTemplate::create([
            'code' => 'QC-GUARD-002',
            'name' => 'QC Single Guard Template',
            'category' => 'QC',
            'status' => 'active',
        ]);
        $record = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-QC-SINGLE-GUARD',
            'equipment_no' => 'EQ-QC-SINGLE-GUARD',
            'description' => 'QC SINGLE PROTECTED EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $admin->id,
            'form_number' => '002/QC/06-2026',
            'status' => 'pending_approval',
            'general_info' => [
                'master_data_record_id' => $record->id,
            ],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.master-data.update', $record), [
                'document_category' => $record->document_category,
                'year' => $record->year,
                'func_location' => $record->func_location,
                'equipment_no' => $record->equipment_no,
                'section_no' => $record->section_no,
                'description' => 'SHOULD NOT BE UPDATED',
                'plant' => $record->plant,
                'area' => $record->area,
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('status');

        $record->refresh();
        $this->assertSame('active', $record->status);
        $this->assertSame('QC SINGLE PROTECTED EQUIPMENT', $record->description);
        $this->assertDatabaseCount('master_data_status_histories', 0);
    }

    public function test_filtered_deactivation_skips_commissioning_equipment_used_by_active_submission(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-GUARD-001',
            'name' => 'Commissioning Guard Template',
            'category' => 'Commissioning',
            'status' => 'active',
        ]);
        $protectedRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-GUARD-PROTECTED',
            'equipment_no' => 'EQ-COM-GUARD-PROTECTED',
            'description' => 'COMMISSIONING FILTER GUARD',
            'plant' => 'TONASA 4',
            'area' => 'KILN 4',
            'status' => 'active',
        ]);
        $eligibleRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-GUARD-ELIGIBLE',
            'equipment_no' => 'EQ-COM-GUARD-ELIGIBLE',
            'description' => 'COMMISSIONING FILTER GUARD',
            'plant' => 'TONASA 4',
            'area' => 'KILN 4',
            'status' => 'active',
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $admin->id,
            'form_number' => '001/COM/06-2026',
            'status' => 'pending_approval',
            'year' => '2026',
            'area' => 'KILN 4',
            'equipment_no' => $protectedRecord->equipment_no,
            'functional_location' => $protectedRecord->func_location,
            'header_data' => [
                'master_data_record_id' => $protectedRecord->id,
            ],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-filtered-status'), [
                'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
                'year' => '2026',
                'plant' => 'TONASA 4',
                'area' => 'KILN 4',
                'current_status' => 'active',
                'search' => 'COMMISSIONING FILTER GUARD',
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas(
                'success',
                '1 master data hasil filter berhasil diubah menjadi Nonaktif. 1 dilewati karena masih digunakan oleh draft/submission aktif.'
            );

        $this->assertDatabaseHas('master_data_records', [
            'id' => $protectedRecord->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'id' => $eligibleRecord->id,
            'status' => 'inactive',
        ]);

        $history = MasterDataStatusHistory::firstOrFail();
        $this->assertSame($eligibleRecord->id, $history->master_data_record_id);
        $this->assertSame('bulk_filtered', $history->source);
        $this->assertSame($admin->id, $history->changed_by);
        $this->assertSame('ST-COM-GUARD-ELIGIBLE', $history->snapshot['master_data']['func_location']);
    }

    public function test_legacy_submission_uses_functional_location_before_duplicate_equipment_number(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-LEGACY-GUARD',
            'name' => 'Commissioning Legacy Guard',
            'category' => 'Commissioning',
            'status' => 'active',
        ]);
        $usedRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-LEGACY-USED',
            'equipment_no' => 'EQ-DUPLICATE-001',
            'description' => 'LEGACY USED EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'COAL MILL 4',
            'status' => 'active',
        ]);
        $unusedRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-LEGACY-UNUSED',
            'equipment_no' => 'EQ-DUPLICATE-001',
            'description' => 'LEGACY UNUSED EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'COAL MILL 4',
            'status' => 'active',
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $admin->id,
            'form_number' => '002/COM/06-2026',
            'status' => 'draft',
            'equipment_no' => 'EQ-DUPLICATE-001',
            'functional_location' => $usedRecord->func_location,
            'header_data' => [],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-status'), [
                'record_ids' => [$usedRecord->id, $unusedRecord->id],
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas(
                'success',
                '1 master data berhasil diubah menjadi Nonaktif. 1 dilewati karena masih digunakan oleh draft/submission aktif.'
            );

        $this->assertDatabaseHas('master_data_records', [
            'id' => $usedRecord->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'id' => $unusedRecord->id,
            'status' => 'inactive',
        ]);
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

    public function test_admin_can_bulk_update_all_filtered_commissioning_master_data(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $commissioningRecords = collect(range(1, 3))->map(fn (int $index) => MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => "ST-FILTERED-COM-{$index}",
            'equipment_no' => "FILTERED-COM-{$index}",
            'section_no' => "SEC-COM-{$index}",
            'description' => 'FILTERED COMMISSIONING EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'COAL MILL',
            'status' => 'active',
        ]));

        $qcRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-FILTERED-QC-KEEP',
            'equipment_no' => 'FILTERED-QC-KEEP',
            'section_no' => 'SEC-QC-KEEP',
            'description' => 'FILTERED QC EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'COAL MILL',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-filtered-status'), [
                'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
                'year' => '2026',
                'plant' => 'TONASA 4',
                'area' => 'COAL MILL',
                'current_status' => 'active',
                'search' => 'FILTERED',
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '3 master data hasil filter berhasil diubah menjadi Nonaktif.');

        foreach ($commissioningRecords as $record) {
            $this->assertDatabaseHas('master_data_records', ['id' => $record->id, 'status' => 'inactive']);
        }

        $this->assertDatabaseHas('master_data_records', ['id' => $qcRecord->id, 'status' => 'active']);
    }

    public function test_admin_can_bulk_update_all_filtered_master_data_for_all_categories(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $records = collect([
            MasterDataRecord::CATEGORY_QC,
            MasterDataRecord::CATEGORY_COMMISSIONING,
        ])->map(fn (string $category) => MasterDataRecord::create([
            'document_category' => $category,
            'year' => '2026',
            'func_location' => "ST-ALL-{$category}",
            'equipment_no' => "EQ-ALL-{$category}",
            'section_no' => "SEC-ALL-{$category}",
            'description' => 'ALL CATEGORY FILTER EQUIPMENT',
            'plant' => 'TONASA 5',
            'area' => 'KILN',
            'status' => 'active',
        ]));

        $unmatchedRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-ALL-UNMATCHED',
            'equipment_no' => 'EQ-ALL-UNMATCHED',
            'section_no' => 'SEC-ALL-UNMATCHED',
            'description' => 'UNMATCHED EQUIPMENT',
            'plant' => 'TONASA 5',
            'area' => 'PACKER',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.bulk-filtered-status'), [
                'document_category' => 'all',
                'year' => '2026',
                'plant' => 'TONASA 5',
                'area' => 'KILN',
                'current_status' => 'active',
                'search' => 'ALL CATEGORY',
                'status' => 'inactive',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '2 master data hasil filter berhasil diubah menjadi Nonaktif.');

        foreach ($records as $record) {
            $this->assertDatabaseHas('master_data_records', ['id' => $record->id, 'status' => 'inactive']);
        }

        $this->assertDatabaseHas('master_data_records', ['id' => $unmatchedRecord->id, 'status' => 'active']);
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

    public function test_admin_can_reset_inspection_status_to_default(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $record = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-RESET',
            'equipment_no' => 'EQ-COM-RESET',
            'section_no' => 'SEC-COM-RESET',
            'description' => 'RESET STATUS EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.master-data.inspection-status', $record), [
                'inspection_status' => null,
            ])
            ->assertOk()
            ->assertJson([
                'status' => null,
                'label' => 'Pilih Status',
            ]);

        $this->assertDatabaseHas('master_data_records', [
            'id' => $record->id,
            'inspection_status' => null,
        ]);

        $history = MasterDataInspectionStatusHistory::firstOrFail();
        $this->assertSame('close', $history->previous_status);
        $this->assertSame('reset', $history->status);
    }
}
