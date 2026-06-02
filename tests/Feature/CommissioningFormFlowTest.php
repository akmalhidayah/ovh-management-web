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
            ->assertSee('Unit Kerja')
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
        $this->assertSame('Line 2/3 FM Operation', $submission->header_data['unit_kerja']);
        $this->assertSame('Line 2/3 FM Operation', $submission->approval_data['unit_kerja']['label']);
        $this->assertSame('Tampered Unit Kerja', $submission->approval_data['unit_kerja']['name']);
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
        $this->assertStringContainsString("/submissions/{$submission->id}/pdf", $pdfUrl);

        $this->actingAs($user)
            ->get(route('user.commissioning.history.index'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('PDF');

        $this->actingAs($user)
            ->get($pdfUrl)
            ->assertOk();

        $legacyPdfUrl = route('user.commissioning.submissions.pdf', CommissioningFormSubmission::routeKeyFromFormNumber($submission->form_number));

        $this->actingAs($user)
            ->get($legacyPdfUrl)
            ->assertOk();

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
        $this->assertSame('Line 2/3 FM Operation', $steps[2]->label);
        $this->assertSame(1, $steps[0]->links->whereNull('used_at')->whereNull('revoked_at')->count());
    }

    public function test_commissioning_approval_progress_modal_uses_selected_unit_kerja_label(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $submission = CommissioningFormSubmission::with('approvalFlow.steps')->firstOrFail();
        $submission->approvalFlow->steps()->where('step_order', 3)->update(['label' => 'UNIT KERJA']);
        $submission->refresh()->load('approvalFlow.steps');

        $html = view('approvals._progress', [
            'submission' => $submission,
            'modalId' => 'approvalProgressModal'.$submission->id,
        ])->render();

        $this->assertStringContainsString('Line 2/3 FM Operation', $html);
        $this->assertStringNotContainsString('>UNIT KERJA<', $html);
    }

    public function test_commissioning_pdf_uses_selected_unit_kerja_label_before_unit_kerja_step_is_approved(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $submission = CommissioningFormSubmission::with(['template', 'attachments', 'user', 'approvalFlow.steps'])
            ->firstOrFail();

        $html = view('pdf.commissioning-submission', ['submission' => $submission])->render();

        $this->assertStringContainsString('Line 2/3 FM Operation', $html);
        $this->assertStringNotContainsString('>UNIT KERJA<', $html);
    }

    public function test_commissioning_dashboard_lists_global_master_equipment_with_create_action(): void
    {
        Storage::fake('local');
        [$owner, $template, $closedMaster] = $this->makeCommissioningSetup();

        $availableMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-AVAILABLE',
            'equipment_no' => 'EQ-COM-AVAILABLE',
            'section_no' => 'SEC-COM-AVAILABLE',
            'description' => 'AVAILABLE COMMISSIONING MOTOR',
            'plant' => 'TONASA 5',
            'area' => 'KILN',
            'status' => 'active',
        ]);

        $dashboardUser = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);

        $this->actingAs($owner)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $closedMaster, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $this->actingAs($dashboardUser)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('Daftar Equipment Commissioning')
            ->assertSee('Semua Plant')
            ->assertSee('Semua Area')
            ->assertSee('Semua Status')
            ->assertSee('GEARBOX MOTOR')
            ->assertSee('Close')
            ->assertSee('AVAILABLE COMMISSIONING MOTOR')
            ->assertSee('Belum Commissioning')
            ->assertSee('Equipment Commissioning')
            ->assertSee('Close 1 | On Going 0 | Belum Commissioning 1')
            ->assertSee('Buat Commissioning')
            ->assertDontSee('Buat Manual')
            ->assertDontSee('INACTIVE COMMISSIONING')
            ->assertDontSee('Draft Commissioning Saya')
            ->assertSee(e(route('user.commissioning.forms.create', [
                'master_data_record_id' => $availableMaster->id,
                'area' => $availableMaster->area,
            ])), false);

        $this->actingAs($dashboardUser)
            ->get(route('user.commissioning.forms.create', [
                'master_data_record_id' => $availableMaster->id,
                'area' => $availableMaster->area,
            ]))
            ->assertOk()
            ->assertSee('const selectedMasterDataId = "'.$availableMaster->id.'";', false)
            ->assertSee('AVAILABLE COMMISSIONING MOTOR')
            ->assertSee('SEC-COM-AVAILABLE');
    }

    public function test_stale_commissioning_dashboard_create_link_redirects_when_equipment_is_no_longer_available(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();
        $master->update(['inspection_status' => 'close']);

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', [
                'template' => $template->id,
                'master_data_record_id' => $master->id,
                'area' => $master->area,
            ]))
            ->assertRedirect(route('user.commissioning.dashboard'))
            ->assertSessionHas('warning', 'Equipment tersebut sudah dipakai atau di-close. Silakan pilih equipment lain dari daftar terbaru.');
    }

    public function test_commissioning_manual_form_lists_inactive_master_data_by_area(): void
    {
        [$user, $template] = $this->makeCommissioningSetup();
        $inactiveMaster = MasterDataRecord::where('equipment_no', 'EQ-COM-INACTIVE')->firstOrFail();

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('INACTIVE COMMISSIONING')
            ->assertSee('EQ-COM-INACTIVE');

        $payload = $this->payload($template, $inactiveMaster, 'draft');
        unset($payload['attachments']);

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $payload)
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $submission = CommissioningFormSubmission::firstOrFail();
        $inactiveMaster->refresh();

        $this->assertTrue($submission->header_data['master_data_auto_activated']);
        $this->assertSame('inactive', $submission->header_data['master_data_previous_status']);
        $this->assertSame('active', $inactiveMaster->status);
        $this->assertSame('ongoing', $inactiveMaster->inspection_status);

        $this->actingAs($user)
            ->delete(route('user.commissioning.submissions.destroy', $submission))
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $inactiveMaster->refresh();
        $this->assertSame('inactive', $inactiveMaster->status);
        $this->assertNull($inactiveMaster->inspection_status);
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
            ->assertSee('Upload TTD')
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

    public function test_commissioning_master_data_used_by_any_submission_is_hidden_from_new_forms(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $availableMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-LOC-002',
            'equipment_no' => 'EQ-COM-002',
            'section_no' => 'SEC-COM-002',
            'description' => 'AVAILABLE MOTOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'draft'))
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $draft = CommissioningFormSubmission::firstOrFail();

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertDontSee('SEC-COM-001')
            ->assertDontSee('GEARBOX MOTOR')
            ->assertSee('SEC-COM-002')
            ->assertSee('AVAILABLE MOTOR');

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'draft'))
            ->assertSessionHasErrors('header.master_data_record_id');

        $this->assertSame(1, CommissioningFormSubmission::count());

        $this->actingAs($user)
            ->get(route('user.commissioning.submissions.edit', $draft))
            ->assertOk()
            ->assertSee('SEC-COM-001')
            ->assertSee('GEARBOX MOTOR')
            ->assertSee('SEC-COM-002')
            ->assertSee($availableMaster->description);
    }

    public function test_user_editing_commissioning_submission_that_is_no_longer_draft_is_redirected_with_error(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'draft'))
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $submission = CommissioningFormSubmission::firstOrFail();
        $submission->forceFill([
            'status' => 'pending_approval',
            'submitted_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->get(route('user.commissioning.submissions.edit', $submission->fresh()))
            ->assertRedirect(route('user.commissioning.submissions.show', $submission->fresh()))
            ->assertSessionHasErrors(['submission']);
    }

    public function test_user_saving_stale_commissioning_draft_is_redirected_with_error_instead_of_forbidden(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();
        $payload = $this->payload($template, $master, 'draft');

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $payload)
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $submission = CommissioningFormSubmission::firstOrFail();
        $submission->forceFill([
            'status' => 'pending_approval',
            'submitted_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->patch(route('user.commissioning.submissions.update', $submission->fresh()), $payload)
            ->assertRedirect(route('user.commissioning.submissions.show', $submission->fresh()))
            ->assertSessionHasErrors(['submission']);
    }

    public function test_commissioning_rejected_or_cancelled_submission_does_not_hide_master_data_from_new_forms(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '001/COM/05-2026',
            'status' => 'rejected',
            'year' => $master->year,
            'area' => $master->area,
            'equipment' => $master->description,
            'equipment_no' => $master->equipment_no,
            'functional_location' => $master->func_location,
            'header_data' => [
                'master_data_record_id' => $master->id,
                'id_equipment' => $master->equipment_no,
                'functional_location' => $master->func_location,
            ],
        ]);

        $cancelledMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-CANCELLED',
            'equipment_no' => 'EQ-COM-CANCELLED',
            'section_no' => 'SEC-COM-CANCELLED',
            'description' => 'CANCELLED MOTOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '002/COM/05-2026',
            'status' => 'cancelled',
            'year' => $cancelledMaster->year,
            'area' => $cancelledMaster->area,
            'equipment' => $cancelledMaster->description,
            'equipment_no' => $cancelledMaster->equipment_no,
            'functional_location' => $cancelledMaster->func_location,
            'header_data' => [
                'master_data_record_id' => $cancelledMaster->id,
                'id_equipment' => $cancelledMaster->equipment_no,
                'functional_location' => $cancelledMaster->func_location,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('SEC-COM-001')
            ->assertSee('GEARBOX MOTOR')
            ->assertSee('SEC-COM-CANCELLED')
            ->assertSee('CANCELLED MOTOR');

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'draft'))
            ->assertRedirect(route('user.commissioning.drafts.index'));

        $this->assertSame(3, CommissioningFormSubmission::count());
    }

    public function test_commissioning_master_data_closed_manually_is_hidden_from_new_forms(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();
        $master->update(['inspection_status' => 'close']);

        $availableMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-LOC-003',
            'equipment_no' => 'EQ-COM-003',
            'section_no' => 'SEC-COM-003',
            'description' => 'OPEN MOTOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertDontSee('SEC-COM-001')
            ->assertDontSee('GEARBOX MOTOR')
            ->assertSee('SEC-COM-003')
            ->assertSee($availableMaster->description);

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'draft'))
            ->assertSessionHasErrors('header.master_data_record_id');

        $this->assertSame(0, CommissioningFormSubmission::count());
    }

    public function test_commissioning_master_data_ongoing_manually_is_hidden_from_new_forms(): void
    {
        [$user, $template, $master] = $this->makeCommissioningSetup();
        $master->update(['inspection_status' => 'ongoing']);

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertDontSee('SEC-COM-001')
            ->assertDontSee('GEARBOX MOTOR');

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'draft'))
            ->assertSessionHasErrors('header.master_data_record_id');
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
                'body.equipment_check_rows',
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

    public function test_deleting_commissioning_submission_resets_master_status_and_removes_attachment_files(): void
    {
        Storage::fake('local');
        [$user, $template, $master] = $this->makeCommissioningSetup();

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $submission = CommissioningFormSubmission::with('attachments')->firstOrFail();
        $attachmentPath = $submission->attachments->firstOrFail()->file_path;

        $master->refresh();
        $this->assertSame('close', $master->inspection_status);
        Storage::disk('local')->assertExists($attachmentPath);

        $this->actingAs($user)
            ->delete(route('user.commissioning.submissions.destroy', $submission))
            ->assertRedirect(route('user.commissioning.history.index'))
            ->assertSessionHas('success', 'Form Commissioning berhasil dihapus.');

        $this->assertSoftDeleted('commissioning_form_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('commissioning_form_submission_attachments', ['file_path' => $attachmentPath]);
        $this->assertNull($master->refresh()->inspection_status);
        Storage::disk('local')->assertMissing($attachmentPath);

        $this->actingAs($user)
            ->get(route('user.commissioning.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('SEC-COM-001')
            ->assertSee('GEARBOX MOTOR');
    }

    public function test_admin_can_permanently_delete_commissioning_submission_with_related_files(): void
    {
        Storage::fake('local');

        [$user, $template, $master] = $this->makeCommissioningSetup();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($user)
            ->post(route('user.commissioning.forms.store'), $this->payload($template, $master, 'submit'))
            ->assertRedirect(route('user.commissioning.history.index'));

        $submission = CommissioningFormSubmission::with(['attachments', 'approvalFlow.steps'])->firstOrFail();
        $attachmentPath = $submission->attachments->firstOrFail()->file_path;
        $approvalFlowId = $submission->approvalFlow->id;
        $approvalEventIds = $submission->approvalFlow->events()->pluck('id')->all();
        $approvalStepIds = $submission->approvalFlow->steps->pluck('id')->all();

        $this->assertSame('close', $master->refresh()->inspection_status);
        Storage::disk('local')->assertExists($attachmentPath);

        $this->actingAs($admin)
            ->delete(route('admin.commissioning.submissions.destroy', $submission))
            ->assertRedirect()
            ->assertSessionHas('success', 'Submission Commissioning berhasil dihapus permanen.');

        $this->assertDatabaseMissing('commissioning_form_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('commissioning_form_submission_attachments', ['file_path' => $attachmentPath]);
        $this->assertDatabaseMissing('approval_flows', ['id' => $approvalFlowId]);
        foreach ($approvalEventIds as $eventId) {
            $this->assertDatabaseMissing('approval_events', ['id' => $eventId]);
        }
        foreach ($approvalStepIds as $stepId) {
            $this->assertDatabaseMissing('approval_steps', ['id' => $stepId]);
        }
        $this->assertNull($master->refresh()->inspection_status);
        Storage::disk('local')->assertMissing($attachmentPath);
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
                'unit_kerja' => 'Line 2/3 FM Operation',
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
                'unit_kerja' => ['name' => 'Tampered Unit Kerja', 'date' => '2026-05-08'],
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
