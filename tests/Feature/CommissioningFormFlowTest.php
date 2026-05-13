<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommissioningFormFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_publish_commissioning_template(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.template-form-commissioning.store'), [
            'code' => 'COM-MOTOR-001',
            'name' => 'Template Motor Commissioning',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'draft',
            'equipment_check_rows' => [
                ['no' => 1, 'item' => 'Check coupling alignment'],
                ['no' => 2, 'item' => 'Check motor rotation'],
            ],
        ]);

        $template = CommissioningFormTemplate::where('code', 'COM-MOTOR-001')->firstOrFail();

        $response->assertRedirect(route('admin.template-form-commissioning.preview', $template));
        $this->assertSame('Check coupling alignment', $template->body_schema['equipment_check_rows'][0]['item']);

        $this->actingAs($admin)
            ->patch(route('admin.template-form-commissioning.publish', $template))
            ->assertRedirect()
            ->assertSessionHas('success', 'Template Form Commissioning berhasil diaktifkan.');

        $this->assertDatabaseHas('commissioning_form_templates', [
            'id' => $template->id,
            'status' => 'active',
        ]);
    }

    public function test_commissioning_user_can_submit_and_open_pdf(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Template Motor Commissioning')
            ->assertSee('Pilih Name Equipment')
            ->assertSee('Section No.')
            ->assertSee('GEARBOX MOTOR')
            ->assertSee('EQ-COM-001');

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'))
            ->assertSessionHas('success', 'Form Commissioning berhasil disubmit.');

        $submission = CommissioningFormSubmission::firstOrFail();
        $this->assertSame('submitted', $submission->status);
        $this->assertSame('ST-COM-LOC-001', $submission->functional_location);
        $this->assertSame('SEC-COM-001', $submission->tag_num);
        $this->assertSame('GEARBOX MOTOR', $submission->equipment);
        $this->assertSame('TONASA 4', $submission->header_data['plant']);
        $this->assertSame($user->name, $submission->header_data['inspector_commissioning']);
        $this->assertStringContainsString('/COM/', $submission->form_number);

        $pdfUrl = route('user.commissioning.submissions.pdf', $submission);
        $this->assertStringContainsString(CommissioningFormSubmission::routeKeyFromFormNumber($submission->form_number), $pdfUrl);
        $this->assertStringNotContainsString("/submissions/{$submission->id}/pdf", $pdfUrl);

        $this->actingAs($user)
            ->get(route('user.commissioning.history.index'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('PDF');

        $this->actingAs($user)
            ->get($pdfUrl)
            ->assertOk();

        $this->actingAs($user)
            ->get("/user/commissioning/submissions/{$submission->id}/pdf")
            ->assertNotFound();

        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.commissioning'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('GEARBOX MOTOR');

        $this->actingAs($admin)
            ->get(route('admin.commissioning.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_commissioning_user_can_save_draft_without_complete_data(): void
    {
        [$user, $template] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), [
                'template_id' => $template->id,
                'action' => 'draft',
            ])
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $submission = CommissioningFormSubmission::firstOrFail();
        $this->assertSame('draft', $submission->status);
        $this->assertStringContainsString('/COM/', $submission->form_number);

        $this->actingAs($user)
            ->get(route('user.commissioning.drafts.index'))
            ->assertOk()
            ->assertSee($submission->form_number);
    }

    public function test_commissioning_submit_requires_all_required_sections(): void
    {
        [$user, $template] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->from(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->post(route('user.commissioning.forms.store'), [
                'template_id' => $template->id,
                'action' => 'submit',
            ])
            ->assertRedirect(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors([
                'header.functional_location',
                'body.motor_rating.power_kw',
                'body.motor_test_rows',
                'body.gearbox_rating.power_kw',
                'body.gearbox_test_rows',
                'body.equipment_check_rows',
                'note',
                'attachments.dokumentasi',
                'approval.commissioning_leader.name',
            ]);

        $this->assertSame(0, CommissioningFormSubmission::count());
    }

    public function test_commissioning_attachment_rejects_pdf_and_non_image_files(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();
        $payload = $this->payload($template, $master, 'submit');
        $payload['attachments'] = [
            'dokumentasi' => [UploadedFile::fake()->create('manual.pdf', 1, 'application/pdf')],
        ];

        $this->actingAs($user)
            ->from(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->post(route('user.commissioning.forms.store'), $payload)
            ->assertRedirect(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors('attachments.dokumentasi.0');
    }

    private function makeCommissioningSetup(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);

        $template = CommissioningFormTemplate::create([
            'code' => 'COM-MOTOR-001',
            'name' => 'Template Motor Commissioning',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [
                'equipment_check_rows' => [
                    ['no' => 1, 'item' => 'Check motor rotation'],
                ],
            ],
        ]);

        $master = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-LOC-001',
            'equipment_no' => 'EQ-COM-001',
            'section_no' => 'SEC-COM-001',
            'description' => 'GEARBOX MOTOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-INACTIVE',
            'equipment_no' => 'EQ-COM-INACTIVE',
            'section_no' => 'SEC-COM-INACTIVE',
            'description' => 'INACTIVE COMMISSIONING',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'inactive',
        ]);

        return [$user, $template, $master];
    }

    private function payload(CommissioningFormTemplate $template, MasterDataRecord $master, string $action): array
    {
        return [
            'template_id' => $template->id,
            'action' => $action,
            'header' => [
                'master_data_record_id' => $master->id,
                'date_time' => '2026-05-08T10:00',
            ],
            'body' => [
                'motor_rating' => ['power_kw' => '15', 'current_a' => '10', 'voltage_v' => '380', 'freq_hz' => '50', 'brand' => 'ABB'],
                'motor_test_rows' => [
                    ['starting_current' => '12', 'time' => '10', 'r' => '1', 's' => '1', 't' => '1', 'horizontal' => '2', 'vertical' => '2', 'axial' => '2', 'remarks' => 'OK'],
                ],
                'gearbox_rating' => ['power_kw' => '15', 'torque_nm' => '90', 'brand' => 'SEW'],
                'gearbox_test_rows' => [
                    ['time' => '10', 'temperature' => '40', 'horizontal' => '2', 'vertical' => '2', 'axial' => '2', 'remarks' => 'OK'],
                ],
                'equipment_check_rows' => [
                    ['no' => '1', 'item' => 'Check motor rotation', 'check' => '1', 'result' => 'YES', 'remark' => 'OK'],
                ],
            ],
            'note' => 'Commissioning aman',
            'attachments' => [
                'dokumentasi' => [UploadedFile::fake()->image('commissioning.jpg')],
            ],
            'approval' => [
                'commissioning_leader' => ['name' => 'Leader A', 'date' => '2026-05-08'],
                'coordinator_commissioning_qc' => ['name' => 'Coordinator A', 'date' => '2026-05-08'],
                'unit_kerja' => ['name' => 'Unit Kerja A', 'date' => '2026-05-08'],
                'overhaul_management' => ['name' => 'Overhaul A', 'date' => '2026-05-08'],
            ],
        ];
    }
}
