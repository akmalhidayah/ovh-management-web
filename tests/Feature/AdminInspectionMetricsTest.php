<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
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
        $this->assertSame(7, $row->remarks_count);
        $this->assertCount(7, $row->remarks);
        $this->assertSame('Motor Test Report', $row->remarks[0]['section']);
        $this->assertSame('Equipment Check Data', $row->remarks[6]['section']);
        $this->assertSame('Safety guard', $row->remarks[6]['item']);
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
                'catatan' => '',
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
        $this->assertSame(3, $row->remarks_count);
        $this->assertCount(3, $row->remarks);
        $this->assertSame('QC Brics', $row->remarks[0]['section']);
        $this->assertSame('Surface condition', $row->remarks[0]['item']);
        $this->assertSame('QC General', $row->remarks[2]['section']);
        $this->assertSame('Alignment belum sesuai', $row->remarks[2]['text']);
    }
}
