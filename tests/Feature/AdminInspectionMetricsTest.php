<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use App\Support\AdminInspectionSubmissionPageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminInspectionMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_commissioning_equipment_card_uses_total_master_equipment_rows(): void
    {
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-METRIC-001',
            'name' => 'Metric Template',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => ['equipment_check_rows' => []],
        ]);

        collect(['EQ-COM-001', 'EQ-COM-002', 'EQ-COM-003'])->each(fn (string $equipmentNo) => MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => "LOC-{$equipmentNo}",
            'equipment_no' => $equipmentNo,
            'section_no' => "SEC-{$equipmentNo}",
            'description' => "Equipment {$equipmentNo}",
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]));

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'form_number' => '001/COM/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'Equipment EQ-COM-001',
            'equipment_no' => 'EQ-COM-001',
            'header_data' => ['plant' => 'TONASA 4'],
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'form_number' => '002/COM/05-2026',
            'status' => 'draft',
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'Equipment EQ-COM-002',
            'equipment_no' => 'EQ-COM-002',
            'header_data' => ['plant' => 'TONASA 4'],
        ]);

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET', [
                'year' => '2026',
                'plant' => 'TONASA 4',
            ]),
            'commissioning'
        );

        $this->assertSame(3, $data['inspectionMetrics']['cards']['total']);
        $this->assertSame(1, $data['inspectionMetrics']['cards']['process']);
        $this->assertSame(1, $data['inspectionMetrics']['cards']['ongoing']);
        $this->assertSame(33.3, $data['inspectionMetrics']['cards']['percentage']);
        $this->assertSame(3, $data['inspectionMetrics']['areaRows']->sum('equipment'));
    }

    public function test_commissioning_admin_rows_collect_remarks_and_count_one_remarked_form(): void
    {
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-REMARK-001',
            'name' => 'Remark Template',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => ['equipment_check_rows' => []],
        ]);

        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'LOC-COM-REMARK',
            'equipment_no' => 'EQ-COM-REMARK',
            'section_no' => 'SEC-COM-REMARK',
            'description' => 'Equipment Remark',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'form_number' => '010/COM/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'area' => 'RAW MILL',
            'equipment' => 'Equipment Remark',
            'equipment_no' => 'EQ-COM-REMARK',
            'functional_location' => 'LOC-COM-REMARK',
            'header_data' => [
                'master_data_record_id' => $master->id,
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
            'body_data' => [
                'motor_test_rows' => [
                    ['remarks' => 'Motor vibration high'],
                    ['remarks' => 'Motor current imbalance'],
                    ['remarks' => ''],
                ],
                'gearbox_test_rows' => [
                    ['remarks' => 'Gearbox temperature high'],
                    ['remarks' => 'Oil leak at seal'],
                ],
                'equipment_check_rows' => [
                    ['no' => '1', 'item' => 'Visual inspection', 'result' => 'NO', 'remark' => 'Paint damage'],
                    ['no' => '2', 'item' => 'Mounting bolt', 'result' => 'NO', 'remark' => 'Bolt loose'],
                    ['no' => '3', 'item' => 'Safety guard', 'result' => 'NO', 'remark' => 'Guard missing'],
                    ['no' => '4', 'item' => 'Running test', 'result' => 'YES', 'remark' => 'Running normal'],
                ],
            ],
        ]);

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET', [
                'year' => '2026',
                'plant' => 'TONASA 4',
            ]),
            'commissioning'
        );

        $row = $data['submissions']->getCollection()->first();

        $this->assertSame(1, $data['inspectionMetrics']['remarkForms']);
        $this->assertSame('010/COM/05-2026', $row->form_number);
        $this->assertSame(8, $row->remarks_count);
        $this->assertCount(8, $row->remarks);
        $this->assertSame('Motor Test Report', $row->remarks[0]['section']);
        $this->assertSame('Equipment Check Data', $row->remarks[6]['section']);
        $this->assertSame('Safety guard', $row->remarks[6]['item']);

        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $this->actingAs($admin);
        $html = view('modules.qc-content', $data)->render();

        $this->assertStringContainsString('admin-remarks-item is-no', $html);
        $this->assertStringContainsString('admin-remarks-item is-yes', $html);
        $this->assertStringNotContainsString('>Row 1<', $html);
        $this->assertStringNotContainsString('>NO</span>', $html);
        $this->assertStringNotContainsString('>YES</span>', $html);
    }

    public function test_qc_equipment_card_uses_total_master_equipment_rows(): void
    {
        $template = QcFormTemplate::create([
            'code' => 'QC-METRIC-001',
            'name' => 'Metric Template',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);

        collect(['EQ-QC-001', 'EQ-QC-002', 'EQ-QC-003'])->each(fn (string $equipmentNo) => MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => "LOC-{$equipmentNo}",
            'equipment_no' => $equipmentNo,
            'section_no' => "SEC-{$equipmentNo}",
            'description' => "Equipment {$equipmentNo}",
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]));

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '001/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'Equipment EQ-QC-001',
            'general_info' => [
                'id_equipment' => 'EQ-QC-001',
                'name_equipment' => 'Equipment EQ-QC-001',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '002/QC/05-2026',
            'status' => 'draft',
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'Equipment EQ-QC-002',
            'general_info' => [
                'id_equipment' => 'EQ-QC-002',
                'name_equipment' => 'Equipment EQ-QC-002',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.qc'), 'GET', [
                'year' => '2026',
                'plant' => 'TONASA 4',
            ]),
            'qc'
        );

        $this->assertSame(3, $data['inspectionMetrics']['cards']['total']);
        $this->assertSame(1, $data['inspectionMetrics']['cards']['process']);
        $this->assertSame(1, $data['inspectionMetrics']['cards']['ongoing']);
        $this->assertSame(33.3, $data['inspectionMetrics']['cards']['percentage']);
        $this->assertSame(3, $data['inspectionMetrics']['areaRows']->sum('equipment'));
    }

    public function test_qc_admin_rows_collect_row_catatan_as_remarks_and_count_one_remarked_form(): void
    {
        $template = QcFormTemplate::create([
            'code' => 'QC-REMARK-001',
            'name' => 'QC Remark Template',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);

        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'LOC-QC-REMARK',
            'equipment_no' => 'EQ-QC-REMARK',
            'section_no' => 'SEC-QC-REMARK',
            'description' => 'QC Equipment Remark',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '010/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'QC Equipment Remark',
            'general_info' => [
                'master_data_record_id' => $master->id,
                'id_equipment' => 'EQ-QC-REMARK',
                'name_equipment' => 'QC Equipment Remark',
                'functional_location' => 'LOC-QC-REMARK',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $submission->rows()->createMany([
            [
                'block_type' => 'brics_check',
                'order_no' => 1,
                'row_data' => ['key' => 'surface_condition', 'label' => 'Surface condition'],
                'status_value' => 'NO',
                'catatan' => 'Permukaan retak',
            ],
            [
                'block_type' => 'castable_monitoring',
                'order_no' => 2,
                'row_data' => ['no' => '2', 'item' => 'Water mixing'],
                'catatan' => 'Kadar air terlalu tinggi',
            ],
            [
                'block_type' => 'general',
                'order_no' => 3,
                'row_data' => ['item_pengecekan' => 'Alignment'],
                'status_value' => 'Not Ok',
                'catatan' => 'Alignment belum sesuai',
            ],
            [
                'block_type' => 'general',
                'order_no' => 4,
                'row_data' => ['item_pengecekan' => 'Cleanliness'],
                'status_value' => 'Ok',
                'catatan' => 'Area bersih',
            ],
            [
                'block_type' => 'welding_result',
                'order_no' => 5,
                'row_data' => ['deskripsi' => 'Visual welding'],
                'status_value' => 'Baik',
                'catatan' => 'Hasil las baik',
            ],
            [
                'block_type' => 'welding_result',
                'order_no' => 6,
                'row_data' => ['deskripsi' => 'Undercut'],
                'status_value' => 'Perlu Perbaikan',
                'catatan' => 'Undercut perlu diperbaiki',
            ],
            [
                'block_type' => 'welding_result',
                'order_no' => 7,
                'row_data' => ['deskripsi' => 'Crack inspection'],
                'status_value' => 'Tidak Layak',
                'catatan' => 'Ditemukan crack',
            ],
            [
                'block_type' => 'brics_check',
                'order_no' => 8,
                'row_data' => ['key' => 'joint_condition', 'label' => 'Joint condition'],
                'status_value' => 'OK',
                'catatan' => 'Joint baik',
            ],
        ]);

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.qc'), 'GET', [
                'year' => '2026',
                'plant' => 'TONASA 4',
            ]),
            'qc'
        );

        $row = $data['submissions']->getCollection()->first();

        $this->assertSame(1, $data['inspectionMetrics']['remarkForms']);
        $this->assertSame('010/QC/05-2026', $row->form_number);
        $this->assertSame(8, $row->remarks_count);
        $this->assertCount(8, $row->remarks);
        $this->assertSame('QC Brics', $row->remarks[0]['section']);
        $this->assertSame('Surface condition', $row->remarks[0]['item']);
        $this->assertSame('QC General', $row->remarks[2]['section']);
        $this->assertSame('Alignment belum sesuai', $row->remarks[2]['text']);

        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $this->actingAs($admin);
        $html = view('modules.qc-content', $data)->render();

        $this->assertSame(3, substr_count($html, 'admin-remarks-item is-yes'));
        $this->assertSame(4, substr_count($html, 'admin-remarks-item is-no'));
    }

    public function test_qc_admin_status_dropdown_can_return_to_default_status(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = QcFormTemplate::create([
            'code' => 'QC-RESET-001',
            'name' => 'QC Reset Template',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);
        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'LOC-QC-RESET',
            'equipment_no' => 'EQ-QC-RESET',
            'section_no' => 'SEC-QC-RESET',
            'description' => 'QC Reset Equipment',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '020/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'QC Reset Equipment',
            'general_info' => [
                'master_data_record_id' => $master->id,
                'id_equipment' => 'EQ-QC-RESET',
                'name_equipment' => 'QC Reset Equipment',
                'functional_location' => 'LOC-QC-RESET',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.qc'))
            ->assertOk()
            ->assertSee('data-inspection-status-select', false)
            ->assertSee('<option value=""', false)
            ->assertDontSee('<option value="" selected disabled>Pilih Status</option>', false);

        $this->actingAs($admin)
            ->patchJson(route('admin.master-data.inspection-status', $master), [
                'inspection_status' => null,
            ])
            ->assertOk()
            ->assertJson([
                'status' => null,
                'label' => 'Pilih Status',
            ]);

        $this->assertDatabaseHas('master_data_records', [
            'id' => $master->id,
            'inspection_status' => null,
        ]);
    }

    public function test_admin_table_can_filter_by_active_approval_step_and_colors_stage_label(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = QcFormTemplate::create([
            'code' => 'QC-APPROVAL-FILTER',
            'name' => 'QC Approval Filter Template',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);

        $firstSubmission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '021/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now()->subMinute(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'Approval One Equipment',
            'general_info' => [
                'id_equipment' => 'EQ-QC-APPROVAL-1',
                'name_equipment' => 'Approval One Equipment',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);
        $secondSubmission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '022/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'Approval Two Equipment',
            'general_info' => [
                'id_equipment' => 'EQ-QC-APPROVAL-2',
                'name_equipment' => 'Approval Two Equipment',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $this->makeApprovalFlow($firstSubmission, 1, 4);
        $this->makeApprovalFlow($secondSubmission, 2, 4);

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.qc'), 'GET', [
                'approval_progress' => 'approver-3',
            ]),
            'qc'
        );

        $this->assertSame(['022/QC/05-2026'], $data['submissions']->getCollection()->pluck('form_number')->all());
        $this->assertContains('Approver 2', $data['filterOptions']['approvalProgress']->pluck('label')->all());
        $this->assertContains('Approver 3', $data['filterOptions']['approvalProgress']->pluck('label')->all());

        $this->actingAs($admin);
        $html = view('modules.qc-content', $data)->render();

        $this->assertStringContainsString('value="approver-3" selected', $html);
        $this->assertStringContainsString('Approver 3', $html);
        $this->assertStringContainsString('TTD 2/4', $html);
        $this->assertStringContainsString('admin-approval-progress-3', $html);
        $this->assertStringNotContainsString('021/QC/05-2026', $html);
    }

    public function test_commissioning_master_row_without_submission_shows_reset_status_action(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'LOC-COM-RESET-ONLY',
            'equipment_no' => 'EQ-COM-RESET-ONLY',
            'section_no' => 'SEC-COM-RESET-ONLY',
            'description' => 'Commissioning Reset Only Equipment',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.commissioning'))
            ->assertOk()
            ->assertSee('Commissioning Reset Only Equipment')
            ->assertSee('data-reset-inspection-status-button', false)
            ->assertSee('Reset Status Equipment')
            ->assertDontSee('data-delete-label="Commissioning Reset Only Equipment"', false);

        $this->actingAs($admin)
            ->patchJson(route('admin.master-data.inspection-status', $master), [
                'inspection_status' => null,
            ])
            ->assertOk()
            ->assertJson([
                'status' => null,
                'label' => 'Pilih Status',
            ]);

        $this->assertDatabaseHas('master_data_records', [
            'id' => $master->id,
            'inspection_status' => null,
        ]);
    }

    public function test_commissioning_admin_rows_can_filter_by_area_and_sort_by_name_or_area(): void
    {
        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'LOC-COM-SORT-ZETA',
            'equipment_no' => 'EQ-COM-SORT-ZETA',
            'section_no' => 'SEC-COM-SORT-ZETA',
            'description' => 'Zeta Commissioning Equipment',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);
        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'LOC-COM-SORT-ALPHA',
            'equipment_no' => 'EQ-COM-SORT-ALPHA',
            'section_no' => 'SEC-COM-SORT-ALPHA',
            'description' => 'Alpha Commissioning Equipment',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'ongoing',
        ]);
        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'LOC-COM-SORT-BETA',
            'equipment_no' => 'EQ-COM-SORT-BETA',
            'section_no' => 'SEC-COM-SORT-BETA',
            'description' => 'Beta Commissioning Equipment',
            'plant' => 'TONASA 4',
            'area' => 'COAL MILL',
            'status' => 'active',
        ]);

        $areaFiltered = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET', [
                'area' => 'RAW MILL',
                'sort' => 'name_asc',
            ]),
            'commissioning'
        );

        $this->assertSame('RAW MILL', $areaFiltered['filters']['area']);
        $this->assertSame('name_asc', $areaFiltered['filters']['sort']);
        $this->assertSame(
            ['Alpha Commissioning Equipment', 'Zeta Commissioning Equipment'],
            $areaFiltered['submissions']->getCollection()->pluck('equipment')->all()
        );
        $this->assertSame(2, $areaFiltered['inspectionMetrics']['cards']['total']);

        $statusFiltered = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET', [
                'area' => 'RAW MILL',
                'work_status' => 'close',
            ]),
            'commissioning'
        );

        $this->assertSame(
            ['Zeta Commissioning Equipment'],
            $statusFiltered['submissions']->getCollection()->pluck('equipment')->all()
        );
        $this->assertSame(2, $statusFiltered['inspectionMetrics']['cards']['total']);

        $areaSorted = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET', [
                'sort' => 'area_desc',
            ]),
            'commissioning'
        );

        $this->assertSame(
            ['RAW MILL', 'RAW MILL', 'COAL MILL'],
            $areaSorted['submissions']->getCollection()->pluck('area')->all()
        );
    }

    public function test_commissioning_metrics_put_kiln_4_last_in_table_and_chart(): void
    {
        foreach (['RAW MILL 4', 'KILN 4', 'COAL MILL 4'] as $index => $area) {
            MasterDataRecord::create([
                'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
                'year' => '2026',
                'func_location' => 'LOC-COM-AREA-ORDER-'.$index,
                'equipment_no' => 'EQ-COM-AREA-ORDER-'.$index,
                'section_no' => 'SEC-COM-AREA-ORDER-'.$index,
                'description' => 'Area Order Equipment '.$index,
                'plant' => 'TONASA 4',
                'area' => $area,
                'status' => 'active',
                'inspection_status' => 'close',
            ]);
        }

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET'),
            'commissioning'
        );

        $expectedAreas = ['COAL MILL 4', 'RAW MILL 4', 'KILN 4'];

        $this->assertSame($expectedAreas, $data['inspectionMetrics']['areaRows']->pluck('area')->all());
        $this->assertSame($expectedAreas, $data['inspectionMetrics']['chart']['labels']->all());
    }

    public function test_commissioning_search_by_master_equipment_field_keeps_submission_actions(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-SEARCH-ACTION',
            'name' => 'Search Action Template',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => ['equipment_check_rows' => []],
        ]);
        $matchedMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-4302-KL-414-FG01',
            'equipment_no' => '50006115',
            'section_no' => 'SEC-COM-SEARCH-ACTION',
            'description' => 'Flow Gate',
            'plant' => 'TONASA 4',
            'area' => 'KILN 4',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);
        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-4302-KL-414-FG03',
            'equipment_no' => '50006117',
            'section_no' => 'SEC-COM-SEARCH-OTHER',
            'description' => 'Flow Gate',
            'plant' => 'TONASA 4',
            'area' => 'KILN 4',
            'status' => 'active',
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'form_number' => '030/COM/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'area' => 'KILN 4',
            'equipment' => 'Flow Gate',
            'equipment_no' => '50006115',
            'functional_location' => 'ST-4302-KL-414-FG01',
            'header_data' => [
                'master_data_record_id' => $matchedMaster->id,
                'plant' => 'TONASA 4',
                'area' => 'KILN 4',
            ],
        ]);

        $data = AdminInspectionSubmissionPageData::make(
            Request::create(route('admin.commissioning'), 'GET', [
                'search' => '414FG',
            ]),
            'commissioning'
        );

        $row = $data['submissions']->getCollection()->firstWhere('equipment_no', '50006115');

        $this->assertSame(2, $data['inspectionMetrics']['cards']['total']);
        $this->assertCount(2, $data['submissions']->getCollection());
        $this->assertSame('030/COM/05-2026', $row->form_number);
        $this->assertNotNull($row->model);
        $this->assertNotNull($row->pdf_route);

        $this->actingAs($admin);
        $html = view('modules.qc-content', $data)->render();

        $this->assertStringContainsString('030/COM/05-2026', $html);
        $this->assertStringContainsString('data-admin-delete-submission-form', $html);
        $this->assertStringContainsString('bi-filetype-pdf', $html);
        $this->assertStringContainsString('Menunggu Approval', $html);
    }

    public function test_admin_can_restore_qc_submission_to_draft(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = QcFormTemplate::create([
            'code' => 'QC-RESTORE-DRAFT',
            'name' => 'QC Restore Draft',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);
        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'LOC-QC-DRAFT',
            'equipment_no' => 'EQ-QC-DRAFT',
            'section_no' => 'SEC-QC-DRAFT',
            'description' => 'QC Restore Equipment',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '040/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'QC Restore Equipment',
            'general_info' => [
                'master_data_record_id' => $master->id,
                'id_equipment' => 'EQ-QC-DRAFT',
                'functional_location' => 'LOC-QC-DRAFT',
                'name_equipment' => 'QC Restore Equipment',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.qc.submissions.restore-draft', $submission))
            ->assertRedirect();

        $this->assertDatabaseHas('qc_form_submissions', [
            'id' => $submission->id,
            'status' => 'draft',
            'submitted_at' => null,
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'id' => $master->id,
            'inspection_status' => 'ongoing',
        ]);
    }

    public function test_admin_can_restore_commissioning_submission_to_draft(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-RESTORE-DRAFT',
            'name' => 'Commissioning Restore Draft',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => ['equipment_check_rows' => []],
        ]);
        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'LOC-COM-DRAFT',
            'equipment_no' => 'EQ-COM-DRAFT',
            'section_no' => 'SEC-COM-DRAFT',
            'description' => 'Commissioning Restore Equipment',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
            'inspection_status' => 'close',
        ]);
        $submission = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'form_number' => '040/COM/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'area' => 'RAW MILL',
            'equipment' => 'Commissioning Restore Equipment',
            'equipment_no' => 'EQ-COM-DRAFT',
            'functional_location' => 'LOC-COM-DRAFT',
            'header_data' => [
                'master_data_record_id' => $master->id,
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.commissioning.submissions.restore-draft', $submission))
            ->assertRedirect();

        $this->assertDatabaseHas('commissioning_form_submissions', [
            'id' => $submission->id,
            'status' => 'draft',
            'submitted_at' => null,
        ]);
        $this->assertDatabaseHas('master_data_records', [
            'id' => $master->id,
            'inspection_status' => 'ongoing',
        ]);
    }

    public function test_approval_admin_cannot_see_or_use_qc_delete_action(): void
    {
        $approval = User::factory()->create(['usertype' => 'admin', 'role' => 'approval']);
        $template = QcFormTemplate::create([
            'code' => 'QC-APPROVAL-NO-DELETE',
            'name' => 'QC Approval No Delete',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '030/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'QC Approval Equipment',
            'general_info' => [
                'id_equipment' => 'EQ-QC-APPROVAL',
                'name_equipment' => 'QC Approval Equipment',
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $this->actingAs($approval)
            ->get(route('admin.qc'))
            ->assertOk()
            ->assertDontSee('data-admin-delete-submission-form', false)
            ->assertDontSee('Hapus Permanen');

        $this->actingAs($approval)
            ->delete(route('admin.qc.submissions.destroy', $submission))
            ->assertForbidden();

        $this->actingAs($approval)
            ->patch(route('admin.qc.submissions.restore-draft', $submission))
            ->assertForbidden();

        $this->assertDatabaseHas('qc_form_submissions', ['id' => $submission->id]);
    }

    public function test_approval_admin_cannot_delete_commissioning_submission_directly(): void
    {
        $approval = User::factory()->create(['usertype' => 'admin', 'role' => 'approval']);
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-APPROVAL-NO-DELETE',
            'name' => 'Commissioning Approval No Delete',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => ['equipment_check_rows' => []],
        ]);
        $submission = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'form_number' => '030/COM/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'area' => 'RAW MILL',
            'equipment' => 'Commissioning Approval Equipment',
            'equipment_no' => 'EQ-COM-APPROVAL',
            'header_data' => [
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
            ],
        ]);

        $this->actingAs($approval)
            ->delete(route('admin.commissioning.submissions.destroy', $submission))
            ->assertForbidden();

        $this->actingAs($approval)
            ->patch(route('admin.commissioning.submissions.restore-draft', $submission))
            ->assertForbidden();

        $this->assertDatabaseHas('commissioning_form_submissions', ['id' => $submission->id]);
    }

    private function makeApprovalFlow($submission, int $approvedSteps, int $totalSteps): ApprovalFlow
    {
        $flow = ApprovalFlow::create([
            'approvable_type' => $submission->getMorphClass(),
            'approvable_id' => $submission->getKey(),
            'status' => 'pending',
            'current_step_order' => min($approvedSteps + 1, $totalSteps),
        ]);

        foreach (range(1, $totalSteps) as $stepOrder) {
            ApprovalStep::create([
                'approval_flow_id' => $flow->id,
                'step_order' => $stepOrder,
                'label' => "Approver {$stepOrder}",
                'status' => $stepOrder <= $approvedSteps
                    ? ApprovalStep::STATUS_APPROVED
                    : ($stepOrder === $approvedSteps + 1 ? ApprovalStep::STATUS_ACTIVE : ApprovalStep::STATUS_PENDING),
            ]);
        }

        return $flow;
    }
}
