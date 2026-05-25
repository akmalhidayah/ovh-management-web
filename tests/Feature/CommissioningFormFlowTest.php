<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataInspectionStatusHistory;
use App\Models\MasterDataRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_commissioning_template_code_uses_manual_middle_segment_and_sequence(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $payload = [
            'code' => 'MTR',
            'name' => 'Template Motor Commissioning',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'draft',
            'equipment_check_rows' => [
                ['no' => 1, 'item' => 'Check coupling alignment'],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.template-form-commissioning.store'), $payload)
            ->assertRedirect();

        $payload['name'] = 'Template Motor Commissioning 2';

        $this->actingAs($admin)
            ->post(route('admin.template-form-commissioning.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('commissioning_form_templates', ['code' => 'COM-MTR-001']);
        $this->assertDatabaseHas('commissioning_form_templates', ['code' => 'COM-MTR-002']);
    }

    public function test_commissioning_user_can_submit_and_open_pdf(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Template Motor Commissioning')
            ->assertSee('Pilih Section')
            ->assertSee('name="header[area]"', false)
            ->assertSee('data-master-area-select', false)
            ->assertSee('Section No.')
            ->assertSee('GEARBOX MOTOR')
            ->assertSee('EQ-COM-001');

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'))
            ->assertSessionHas('success', 'Form Commissioning berhasil disubmit.');

        $submission = CommissioningFormSubmission::firstOrFail();
        $master->refresh();
        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame('close', $master->inspection_status);
        $this->assertNotNull($submission->approvalFlow);
        $this->assertSame('ST-COM-LOC-001', $submission->functional_location);
        $this->assertSame('SEC-COM-001', $submission->tag_num);
        $this->assertSame('GEARBOX MOTOR', $submission->equipment);
        $this->assertSame('TONASA 4', $submission->header_data['plant']);
        $this->assertSame($user->name, $submission->header_data['inspector_commissioning']);
        $this->assertStringContainsString('/COM/', $submission->form_number);
        $this->assertSame($template->code, $submission->template_code);
        $this->assertSame($template->name, $submission->template_name);
        $this->assertSame('1.0', $submission->template_snapshot['version']);
        $this->assertSame($template->body_schema['equipment_check_rows'][0]['item'], $submission->template_snapshot['body_schema']['equipment_check_rows'][0]['item']);
        $this->assertDatabaseHas('document_number_sequences', [
            'category' => 'commissioning',
            'period' => now()->format('m-Y'),
            'last_number' => 1,
        ]);
        $history = MasterDataInspectionStatusHistory::firstOrFail();
        $this->assertSame($master->id, $history->master_data_record_id);
        $this->assertSame('digital_form', $history->source);
        $this->assertSame($submission->id, $history->submission_id);
        $this->assertSame($submission->form_number, $history->snapshot['submission']['form_number']);

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
            ->assertSee('GEARBOX MOTOR')
            ->assertSee('Detail Approval')
            ->assertSee('Salin Link TTD');

        $this->actingAs($admin)
            ->get(route('admin.commissioning.submissions.pdf', $submission))
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.commissioning.submissions.approval-link', $submission))
            ->assertOk()
            ->assertJsonStructure(['url']);
    }

    public function test_commissioning_submit_creates_four_approver_steps_without_submitter_signature(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $submission = CommissioningFormSubmission::with('approvalFlow.steps.links')->firstOrFail();
        $steps = $submission->approvalFlow->steps;

        $this->assertSame('pending_approval', $submission->status);
        $this->assertCount(4, $steps);
        $this->assertFalse($steps[0]->is_submitter_signature);
        $this->assertTrue($steps[0]->requires_magic_link);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[0]->status);
        $this->assertSame(ApprovalStep::STATUS_PENDING, $steps[1]->status);
        $this->assertSame(1, $steps[0]->links->whereNull('used_at')->whereNull('revoked_at')->count());
    }

    public function test_public_approval_approve_advances_commissioning_flow(): void
    {
        Storage::fake('public');
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $submission = CommissioningFormSubmission::firstOrFail();
        $url = $this->actingAs($user)
            ->postJson(route('user.commissioning.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');
        $token = $this->tokenFromUrl($url);

        $this->get(route('public.approval.show', $token))
            ->assertOk()
            ->assertSee('Preview PDF')
            ->assertSee('Tanda Tangani Dokumen')
            ->assertSee('value="Leader A"', false)
            ->assertSee(route('public.approval.pdf', $token), false);

        $this->get(route('public.approval.pdf', $token))
            ->assertOk();

        $approveResponse = $this->post(route('public.approval.approve', $token), [
            'approver_name' => 'Commissioning Lead',
            'approver_position' => 'COMMISSIONING Leader',
            'signature' => $this->validSignatureData(),
        ])->assertRedirect();

        $this->get($approveResponse->headers->get('Location'))
            ->assertOk();

        $submission->refresh()->load('approvalFlow.steps');

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $submission->approvalFlow->steps[0]->status);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $submission->approvalFlow->steps[1]->status);
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

    private function tokenFromUrl(string $url): string
    {
        return basename((string) parse_url($url, PHP_URL_PATH));
    }

    private function validSignatureData(): string
    {
        return 'data:image/png;base64,'.base64_encode(base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        ));
    }
}
