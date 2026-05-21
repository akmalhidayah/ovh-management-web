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
}
