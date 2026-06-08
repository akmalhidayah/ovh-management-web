<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\MasterDataStatusHistory;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\User;
use App\Services\ApprovalFlowService;
use App\Support\QcTemplates\FixedQcTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QcFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_draft_qc_submission(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->payload($template, $block, $row, 'draft'))
            ->assertRedirect(route('user.qc.drafts.index'))
            ->assertSessionHas('success', 'Draft QC berhasil disimpan.');

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('draft', $submission->status);
        $this->assertSame('Crusher Rotor', $submission->equipment);
        $this->assertSame(1, $submission->rows()->count());

        $this->actingAs($user)
            ->get(route('user.qc.drafts.index'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Template Aktif QC');
    }

    public function test_user_can_submit_qc_submission_and_open_pdf(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->payload($template, $block, $row, 'submit'))
            ->assertRedirect(route('user.qc.history.index'))
            ->assertSessionHas('success', 'Form QC berhasil disubmit.');

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('pending_approval', $submission->status);
        $this->assertNotNull($submission->submitted_at);
        $this->assertNotNull($submission->approvalFlow);

        $this->actingAs($user)
            ->get(route('user.qc.history.index'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Menunggu Approval');

        $this->actingAs($user)
            ->get(route('user.qc.submissions.show', $submission))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertDontSee('Tidak Retak');

        $this->actingAs($user)
            ->get(route('user.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_missing_qc_submission_redirects_to_history_with_warning(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $this->actingAs($user)
            ->get(route('user.qc.submissions.show', 999999))
            ->assertRedirect(route('user.qc.history.index'))
            ->assertSessionHas('warning');
    }

    public function test_qc_raw_slash_form_number_url_redirects_to_normalized_url(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedGeneralPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $normalizedKey = QcFormSubmission::routeKeyFromFormNumber($submission->form_number);

        $this->actingAs($user)
            ->get('/user/qc/submissions/'.$submission->form_number)
            ->assertRedirect(route('user.qc.submissions.show', $normalizedKey));
    }

    public function test_duplicate_qc_form_number_returns_clear_form_error(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();
        $duplicateNumber = '777/QC/'.now()->format('m-Y');

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => $duplicateNumber,
            'status' => 'draft',
        ]);

        $payload = $this->payload($template, $block, $row, 'draft');
        $payload['general_info']['report_no'] = $duplicateNumber;

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors('form_number');

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Nomor Form Bentrok', false)
            ->assertSee('showDuplicateFormNumberAlert', false);

        $this->assertSame(1, QcFormSubmission::count());
    }

    public function test_qc_general_submit_creates_auto_inspector_and_four_approver_steps(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedGeneralPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::with('approvalFlow.steps.links')->firstOrFail();
        $steps = $submission->approvalFlow->steps;

        $this->assertSame('pending_approval', $submission->status);
        $this->assertCount(5, $steps);
        $this->assertTrue($steps[0]->is_submitter_signature);
        $this->assertFalse($steps[0]->requires_magic_link);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $steps[0]->status);
        $this->assertNotNull($steps[0]->signature_path);
        $this->assertStringStartsWith('signatures/approval/', $steps[0]->signature_path);
        $this->assertNull($steps[0]->signature_data);
        Storage::disk('public')->assertExists($steps[0]->signature_path);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[1]->status);
        $this->assertSame(1, $steps[1]->links->whereNull('used_at')->whereNull('revoked_at')->count());
        $this->assertSame(0, $steps[2]->links->count());
    }

    public function test_user_cannot_submit_fixed_qc_without_inspector_signature(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        unset($payload['approval']['qc_inspector_q_c_inspektor']);

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors([
                'approval.qc_inspector_q_c_inspektor.signature' => 'Tanda tangan QC Inspektor wajib diisi.',
            ]);

        $this->assertSame(0, QcFormSubmission::count());
    }

    public function test_user_cannot_submit_fixed_qc_without_required_before_after_photos(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        unset($payload['attachments']['foto_before']);

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors([
                'attachments.foto_before' => 'Foto Before wajib diupload. Dokumen Pendukung boleh dikosongkan.',
            ]);

        $this->assertSame(0, QcFormSubmission::count());
    }

    public function test_fixed_qc_general_ignores_tampered_template_item_and_standard(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['body']['general_rows'][0]['item_pengecekan'] = 'Injected Item';
        $payload['body']['general_rows'][0]['standar'] = 'Injected Standard';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('Cek bearing', $submission->body_data['general_rows'][0]['item_pengecekan']);
        $this->assertSame('Normal', $submission->body_data['general_rows'][0]['standar']);
        $this->assertArrayNotHasKey('actual', $submission->body_data['general_rows'][0]);
    }

    public function test_fixed_qc_general_uses_header_unit_kerja_for_unit_kerja_approver(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['unit_kerja'] = 'Line 2/3 FM Operation';
        $payload['approval']['approved_by_unit_kerja'] = [
            'name' => 'Tampered Name',
            'role' => 'Unit Kerja',
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('Line 2/3 FM Operation', $submission->general_info['unit_kerja']);
        $this->assertSame('Mgr of Line 2/3 FM Operation', $submission->approval_data['approved_by_unit_kerja']['label']);
        $this->assertSame('Tampered Name', $submission->approval_data['approved_by_unit_kerja']['name']);
    }

    public function test_fixed_qc_welding_uses_header_unit_kerja_for_unit_kerja_approver(): void
    {
        [$user, $template] = $this->makeFixedWeldingTemplate();
        $payload = $this->fixedWeldingPayload($template);
        $payload['header']['unit_kerja'] = 'Cement Production Coach';
        $payload['approval']['approved_by_unit_kerja'] = [
            'name' => 'Tampered Name',
            'role' => 'Unit Kerja',
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('Cement Production Coach', $submission->general_info['unit_kerja']);
        $this->assertSame('Mgr of Cement Production Coach', $submission->approval_data['approved_by_unit_kerja']['label']);
        $this->assertSame('Tampered Name', $submission->approval_data['approved_by_unit_kerja']['name']);
    }

    public function test_qc_castable_submit_creates_three_step_approval_flow(): void
    {
        [$user, $template] = $this->makeFixedCastableTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedCastablePayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::with('approvalFlow.steps.links')->firstOrFail();
        $steps = $submission->approvalFlow->steps;

        $this->assertSame('pending_approval', $submission->status);
        $this->assertCount(3, $steps);
        $this->assertSame('*1 diisi', $steps[0]->label);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $steps[0]->status);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[1]->status);
        $this->assertSame(ApprovalStep::STATUS_PENDING, $steps[2]->status);
    }

    public function test_public_approval_uses_editable_fixed_qc_title_as_approver_position(): void
    {
        [$user, $castableTemplate] = $this->makeFixedCastableTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedCastablePayload($castableTemplate))
            ->assertRedirect(route('user.qc.history.index'));

        $castableSubmission = QcFormSubmission::firstOrFail();
        $castableUrl = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $castableSubmission))
            ->assertOk()
            ->json('url');

        $this->get(route('public.approval.show', $this->tokenFromUrl($castableUrl)))
            ->assertOk()
            ->assertSee('value="Manager Approval"', false)
            ->assertDontSee('value="*2 disetujui"', false);

        [$bricsUser, $bricsTemplate] = $this->makeFixedBricsTemplate();

        $this->actingAs($bricsUser)
            ->post(route('user.qc.forms.store'), $this->fixedBricsPayload($bricsTemplate))
            ->assertRedirect(route('user.qc.history.index'));

        $bricsSubmission = QcFormSubmission::latest('id')->firstOrFail();
        $bricsUrl = $this->actingAs($bricsUser)
            ->postJson(route('user.qc.submissions.approval-link', $bricsSubmission))
            ->assertOk()
            ->json('url');

        $this->get(route('public.approval.show', $this->tokenFromUrl($bricsUrl)))
            ->assertOk()
            ->assertSee('value="Supplier PIC"', false)
            ->assertDontSee('value="Vendor"', false);
    }

    public function test_public_approval_uses_area_owner_manager_label_for_fixed_qc_unit_kerja_step(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['unit_kerja'] = 'Line 4 RKC Operation';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::with('approvalFlow.steps')->firstOrFail();
        $submission->approvalFlow->steps()->whereIn('step_order', [2, 3])->update([
            'status' => ApprovalStep::STATUS_APPROVED,
        ]);
        $submission->approvalFlow->steps()->where('step_order', 4)->update([
            'status' => ApprovalStep::STATUS_ACTIVE,
        ]);

        $url = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');

        $this->get(route('public.approval.show', $this->tokenFromUrl($url)))
            ->assertOk()
            ->assertSee('value="Mgr of Line 4 RKC Operation"', false)
            ->assertDontSee('value="Line 4 RKC Operation"', false);
    }

    public function test_public_approval_approve_advances_qc_flow_and_invalidates_token(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['approval']['checked_by_q_c_leader']['name'] = 'Leader From Form';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $url = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');
        $token = $this->tokenFromUrl($url);

        $this->get(route('public.approval.show', $token))
            ->assertOk()
            ->assertSee('Preview PDF')
            ->assertSee('Tanda Tangani Dokumen')
            ->assertSee('Upload TTD')
            ->assertSee('value="Leader From Form"', false)
            ->assertSee(route('public.approval.pdf', $token), false);

        $this->get(route('public.approval.pdf', $token))
            ->assertOk();

        $approveResponse = $this->post(route('public.approval.approve', $token), [
            'approver_name' => 'Leader QC',
            'approver_position' => 'QC Leader',
            'signature_file' => UploadedFile::fake()->image('signature.png', 20, 10),
        ])->assertOk()
            ->assertSee('Approval berhasil')
            ->assertSee('Approval berhasil disimpan')
            ->assertSee('Swal.fire', false)
            ->assertSee('Lihat PDF')
            ->assertSee('approval\/signed-pdf', false)
            ->assertSee('window.location.assign', false)
            ->assertDontSee('class="btn btn-primary"', false);

        preg_match('/const signedPdfUrl = ("[^"]+");/', $approveResponse->getContent(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $this->get(json_decode($matches[1], true))
            ->assertOk();

        $submission->refresh()->load('approvalFlow.steps.links');
        $steps = $submission->approvalFlow->steps;

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $steps[1]->status);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[2]->status);
        $this->assertSame(1, $steps[1]->links->whereNotNull('used_at')->count());
        $this->assertNotNull($steps[1]->signature_path);
        $this->assertNull($steps[1]->signature_data);
        Storage::disk('public')->assertExists($steps[1]->signature_path);

        $this->get(route('public.approval.show', $token))
            ->assertNotFound()
            ->assertSee('Link approval sudah digunakan');
    }

    public function test_resubmitting_admin_restored_qc_draft_preserves_approved_approval_steps(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $payload = $this->fixedGeneralPayload($template);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $url = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');

        $this->post(route('public.approval.approve', $this->tokenFromUrl($url)), [
            'approver_name' => 'Leader QC',
            'approver_position' => 'QC Leader',
            'signature_file' => UploadedFile::fake()->image('leader.png', 20, 10),
        ])->assertOk();

        $submission->refresh()->load('approvalFlow.steps');
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $submission->approvalFlow->steps[1]->status);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $submission->approvalFlow->steps[2]->status);

        $this->actingAs($admin)
            ->patch(route('admin.qc.submissions.restore-draft', $submission))
            ->assertRedirect();

        $payload['note'] = 'Updated after admin restore';
        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission->fresh()), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission->refresh()->load('approvalFlow.steps');
        $steps = $submission->approvalFlow->steps;

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $steps[1]->status);
        $this->assertSame('Leader QC', $steps[1]->approver_name);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[2]->status);
        $this->assertNotSame(ApprovalStep::STATUS_ACTIVE, $steps[1]->status);
    }

    public function test_resubmitting_rejected_qc_revision_preserves_previously_approved_steps(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $leaderUrl = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');

        $this->post(route('public.approval.approve', $this->tokenFromUrl($leaderUrl)), [
            'approver_name' => 'Leader QC',
            'approver_position' => 'QC Leader',
            'signature_file' => UploadedFile::fake()->image('leader.png', 20, 10),
        ])->assertOk();

        $unitKerjaUrl = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission->fresh()))
            ->assertOk()
            ->json('url');

        $this->post(route('public.approval.reject', $this->tokenFromUrl($unitKerjaUrl)), [
            'reject_reason' => 'Perlu perbaikan data',
        ])->assertOk();

        $submission->refresh()->load('approvalFlow.steps');
        $this->assertSame('revision_required', $submission->status);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $submission->approvalFlow->steps[1]->status);
        $this->assertSame(ApprovalStep::STATUS_REJECTED, $submission->approvalFlow->steps[2]->status);

        $payload['note'] = 'Updated after rejection';
        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission->refresh()->load('approvalFlow.steps');
        $steps = $submission->approvalFlow->steps;

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $steps[1]->status);
        $this->assertSame('Leader QC', $steps[1]->approver_name);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[2]->status);
        $this->assertNotSame(ApprovalStep::STATUS_ACTIVE, $steps[1]->status);
    }

    public function test_resubmitting_uses_older_approved_steps_when_latest_flow_missed_them(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $payload = $this->fixedGeneralPayload($template);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $leaderUrl = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');

        $this->post(route('public.approval.approve', $this->tokenFromUrl($leaderUrl)), [
            'approver_name' => 'Leader QC',
            'approver_position' => 'QC Leader',
            'signature_file' => UploadedFile::fake()->image('leader.png', 20, 10),
        ])->assertOk();

        $submission->refresh()->load('approvalFlow.steps');
        $historicalSteps = $submission->approvalFlow->steps;
        $this->assertSame(ApprovalStep::STATUS_APPROVED, $historicalSteps[1]->status);

        $brokenFlow = $submission->approvalFlow()->create([
            'status' => ApprovalFlow::STATUS_PENDING,
            'current_step_order' => $historicalSteps[1]->step_order,
        ]);

        foreach ($historicalSteps as $index => $historicalStep) {
            $status = match ($index) {
                0 => ApprovalStep::STATUS_APPROVED,
                1 => ApprovalStep::STATUS_ACTIVE,
                default => ApprovalStep::STATUS_PENDING,
            };

            $brokenFlow->steps()->create([
                'step_order' => $historicalStep->step_order,
                'label' => $historicalStep->label,
                'is_submitter_signature' => $historicalStep->is_submitter_signature,
                'requires_magic_link' => $historicalStep->requires_magic_link,
                'status' => $status,
                'approver_name' => $index === 0 ? $historicalStep->approver_name : null,
                'approver_position' => $index === 0 ? $historicalStep->approver_position : null,
                'signature_path' => $index === 0 ? $historicalStep->signature_path : null,
                'acted_at' => $index === 0 ? $historicalStep->acted_at : null,
            ]);
        }

        $submission->forceFill(['status' => 'pending_approval'])->save();

        $this->actingAs($admin)
            ->patch(route('admin.qc.submissions.restore-draft', $submission))
            ->assertRedirect();

        $payload['note'] = 'Updated after broken latest flow';
        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission->fresh()), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission->refresh()->load('approvalFlow.steps');
        $steps = $submission->approvalFlow->steps;

        $this->assertSame(ApprovalStep::STATUS_APPROVED, $steps[1]->status);
        $this->assertSame('Leader QC', $steps[1]->approver_name);
        $this->assertSame(ApprovalStep::STATUS_ACTIVE, $steps[2]->status);
    }

    public function test_public_approval_reject_marks_qc_submission_revision_required(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedGeneralPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $url = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');

        $this->post(route('public.approval.reject', $this->tokenFromUrl($url)), [
            'reject_reason' => 'Data belum sesuai',
        ])->assertOk();

        $submission->refresh()->load('approvalFlow.steps');

        $this->assertSame('revision_required', $submission->status);
        $this->assertSame('revision_required', $submission->approvalFlow->status);
        $this->assertSame(ApprovalStep::STATUS_REJECTED, $submission->approvalFlow->steps[1]->status);
        $this->assertSame('Data belum sesuai', $submission->approvalFlow->steps[1]->reject_reason);
    }

    public function test_expired_public_approval_link_shows_expired_message(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedGeneralPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $url = $this->actingAs($user)
            ->postJson(route('user.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->json('url');
        $token = $this->tokenFromUrl($url);

        $submission->approvalFlow
            ->steps()
            ->where('status', ApprovalStep::STATUS_ACTIVE)
            ->firstOrFail()
            ->links()
            ->update(['expires_at' => now()->subMinute()]);

        $this->get(route('public.approval.show', $token))
            ->assertNotFound()
            ->assertSee('Link approval sudah kedaluwarsa');
    }

    public function test_admin_can_access_submission_pdf(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->payload($template, $block, $row, 'submit'))
            ->assertRedirect();

        $submission = QcFormSubmission::firstOrFail();
        $submission->forceFill(['plant' => null])->save();

        $this->actingAs($admin)
            ->get(route('admin.qc.submissions.index'))
            ->assertRedirect(route('admin.qc'));

        $this->actingAs($admin)
            ->get(route('admin.qc'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Tonasa 4')
            ->assertSee('Detail Approval')
            ->assertSee('Salin Link TTD');

        $this->actingAs($admin)
            ->get("/admin/qc/submissions/{$submission->id}")
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.qc.submissions.pdf', $submission))
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.qc.submissions.approval-link', $submission))
            ->assertOk()
            ->assertJsonStructure(['url']);
    }

    public function test_user_can_store_fixed_qc_draft_without_complete_data(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), [
                'template_id' => $template->id,
                'action' => 'draft',
            ])
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('draft', $submission->status);
        $this->assertSame([], $submission->body_data['general_rows']);
    }

    public function test_fixed_qc_form_shows_auto_doc_number_and_reordered_header(): void
    {
        Carbon::setTestNow('2026-05-08 10:00:00');

        try {
            [$user, $template] = $this->makeFixedGeneralTemplate();

            $this->actingAs($user)
                ->get(route('user.qc.forms.create', ['template' => $template->id]))
                ->assertOk()
                ->assertSee('001/QC/05-2026')
                ->assertSeeInOrder(['Doc.Number', 'Plant', 'Section', 'Functional Location', 'ID Equipment', 'Name Equipment'], false);

            $this->actingAs($user)
                ->post(route('user.qc.forms.store'), [
                    'template_id' => $template->id,
                    'action' => 'draft',
                ])
                ->assertRedirect(route('user.qc.drafts.index'));

            $submission = QcFormSubmission::firstOrFail();
            $this->assertSame('001/QC/05-2026', $submission->form_number);
            $this->assertSame('001/QC/05-2026', $submission->report_no);
            $this->assertSame('001/QC/05-2026', $submission->general_info['doc_number']);
            $this->assertSame($template->code, $submission->template_code);
            $this->assertSame($template->name, $submission->template_name);
            $this->assertSame('1.0', $submission->template_snapshot['version']);
            $this->assertSame($template->template_type, $submission->template_snapshot['template_type']);
            $this->assertDatabaseHas('document_number_sequences', [
                'category' => 'qc',
                'period' => '05-2026',
                'last_number' => 1,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_fixed_qc_name_equipment_uses_qc_master_data_for_manual_form(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $activeRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-ACTIVE-QC',
            'equipment_no' => 'EQ-ACTIVE-001',
            'section_no' => 'SEC-ACTIVE-001',
            'description' => 'ACTIVE BELT CONVEYOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $inactiveRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-INACTIVE-QC',
            'equipment_no' => 'EQ-INACTIVE-001',
            'section_no' => 'SEC-INACTIVE-001',
            'description' => 'INACTIVE EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'inactive',
        ]);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COMMISSIONING',
            'equipment_no' => 'EQ-COM-001',
            'section_no' => 'SEC-COM-001',
            'description' => 'COMMISSIONING EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Pilih Section')
            ->assertSee('name="header[area]"', false)
            ->assertSee('data-master-area-select', false)
            ->assertSee('ACTIVE BELT CONVEYOR')
            ->assertSee('EQ-ACTIVE-001')
            ->assertSee('INACTIVE EQUIPMENT')
            ->assertSee('EQ-INACTIVE-001')
            ->assertDontSee('ST-COMMISSIONING');

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', [
                'template' => $template->id,
                'master_data_record_id' => $activeRecord->id,
                'area' => $activeRecord->area,
            ]))
            ->assertOk()
            ->assertSee('const selectedMasterDataId = "'.$activeRecord->id.'";', false)
            ->assertSee('ACTIVE BELT CONVEYOR')
            ->assertSee('SEC-ACTIVE-001');

        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['master_data_record_id'] = $activeRecord->id;
        $payload['header']['plant'] = 'PLANT-WRONG';
        $payload['header']['functional_location'] = 'MANUAL-WRONG';
        $payload['header']['tag_num'] = 'TAG-WRONG';
        $payload['header']['area'] = 'AREA-WRONG';
        $payload['header']['id_equipment'] = 'EQ-WRONG';
        $payload['header']['name_equipment'] = 'EQP-WRONG';
        $payload['header']['inspector_qc'] = 'INSPECTOR-WRONG';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame($activeRecord->id, $submission->general_info['master_data_record_id']);
        $this->assertSame('TONASA 4', $submission->general_info['plant']);
        $this->assertSame('ST-ACTIVE-QC', $submission->general_info['functional_location']);
        $this->assertSame('2026', $submission->general_info['tahun']);
        $this->assertSame('SEC-ACTIVE-001', $submission->general_info['tag_num']);
        $this->assertSame('RAW MILL', $submission->general_info['area']);
        $this->assertSame('EQ-ACTIVE-001', $submission->general_info['id_equipment']);
        $this->assertSame('ACTIVE BELT CONVEYOR', $submission->general_info['name_equipment']);
        $this->assertSame($user->name, $submission->general_info['inspector_qc']);
        $this->assertSame('TONASA 4', $submission->plant);
        $this->assertSame('SEC-ACTIVE-001', $submission->tag_num);
        $this->assertSame('RAW MILL', $submission->area);

        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['master_data_record_id'] = $inactiveRecord->id;

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $inactiveSubmission = QcFormSubmission::latest('id')->firstOrFail();
        $inactiveRecord->refresh();

        $this->assertSame($inactiveRecord->id, $inactiveSubmission->general_info['master_data_record_id']);
        $this->assertSame('INACTIVE EQUIPMENT', $inactiveSubmission->general_info['name_equipment']);
        $this->assertSame('ST-INACTIVE-QC', $inactiveSubmission->general_info['functional_location']);
        $this->assertTrue($inactiveSubmission->general_info['master_data_auto_activated']);
        $this->assertSame('inactive', $inactiveSubmission->general_info['master_data_previous_status']);
        $this->assertSame('active', $inactiveRecord->status);
        $this->assertSame('close', $inactiveRecord->inspection_status);
        $this->assertDatabaseHas('master_data_status_histories', [
            'master_data_record_id' => $inactiveRecord->id,
            'previous_status' => 'inactive',
            'status' => 'active',
            'source' => 'digital_form',
            'submission_type' => $inactiveSubmission->getMorphClass(),
            'submission_id' => $inactiveSubmission->id,
            'changed_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('user.qc.submissions.destroy', $inactiveSubmission))
            ->assertRedirect(route('user.qc.history.index'));

        $inactiveRecord->refresh();
        $this->assertSame('inactive', $inactiveRecord->status);
        $this->assertNull($inactiveRecord->inspection_status);
        $this->assertSame(2, MasterDataStatusHistory::where('master_data_record_id', $inactiveRecord->id)->count());
        $this->assertDatabaseHas('master_data_status_histories', [
            'master_data_record_id' => $inactiveRecord->id,
            'previous_status' => 'active',
            'status' => 'inactive',
            'source' => 'submission_deleted',
            'submission_type' => $inactiveSubmission->getMorphClass(),
            'submission_id' => $inactiveSubmission->id,
            'changed_by' => $user->id,
        ]);
    }

    public function test_user_can_continue_fixed_qc_draft_with_saved_data(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';
        $payload['header']['doc_number'] = 'DOC-DRAFT-001';
        $payload['body']['general_rows'][0]['catatan'] = 'Catatan draft general';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertNotSame('DOC-DRAFT-001', $submission->form_number);

        $this->actingAs($user)
            ->get(route('user.qc.submissions.edit', $submission))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Catatan draft general')
            ->assertSee('Catatan fixed general');

        $payload['body']['general_rows'][0]['catatan'] = 'Catatan row diperbarui';
        $payload['note'] = 'Catatan draft diperbarui';

        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission), $payload)
            ->assertRedirect(route('user.qc.drafts.index'))
            ->assertSessionHas('success', 'Draft QC berhasil diperbarui.');

        $submission->refresh();

        $this->assertSame(1, QcFormSubmission::count());
        $this->assertSame('draft', $submission->status);
        $this->assertSame('Catatan row diperbarui', $submission->body_data['general_rows'][0]['catatan']);
        $this->assertSame('Catatan draft diperbarui', $submission->note);
        $this->assertSame(1, $submission->rows()->count());
    }

    public function test_fixed_qc_draft_edit_shows_saved_attachments_and_keeps_signature_without_reupload(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        foreach ([FixedQcTemplate::TYPE_CASTABLE, FixedQcTemplate::TYPE_BRICS] as $type) {
            if ($type === FixedQcTemplate::TYPE_CASTABLE) {
                [$user, $template] = $this->makeFixedCastableTemplate();
                $payload = $this->fixedCastablePayload($template);
                $approvalKey = 'castable_filled_by';
            } else {
                [$user, $template] = $this->makeFixedBricsTemplate();
                $payload = $this->fixedBricsPayload($template);
                $approvalKey = 'brics_report_by';
            }

            $payload['action'] = 'draft';

            $this->actingAs($user)
                ->post(route('user.qc.forms.store'), $payload)
                ->assertRedirect(route('user.qc.drafts.index'));

            $submission = QcFormSubmission::with('attachments')->latest('id')->firstOrFail();
            $beforeAttachment = $submission->attachments->firstWhere('field_key', 'foto_before');
            $storedSignature = $submission->approval_data[$approvalKey]['signature'] ?? '';

            $this->assertNotNull($beforeAttachment);
            $this->assertNotSame('', $storedSignature);
            $this->assertSame(2, $submission->attachments->count());

            $this->actingAs($user)
                ->get(route('user.qc.submissions.edit', $submission))
                ->assertOk()
                ->assertSee('Lampiran tersimpan:')
                ->assertSee('before.jpg')
                ->assertSee('after.jpg')
                ->assertSee(route('user.qc.attachments.show', $beforeAttachment), false)
                ->assertSee('data-existing-attachment-remove', false)
                ->assertSee($storedSignature, false)
                ->assertSee('value="2026-05-15"', false);

            $updatePayload = $payload;
            $updatePayload['approval'][$approvalKey]['signature'] = $storedSignature;
            unset($updatePayload['attachments']);

            $this->actingAs($user)
                ->patch(route('user.qc.submissions.update', $submission), $updatePayload)
                ->assertRedirect(route('user.qc.drafts.index'))
                ->assertSessionHas('success', 'Draft QC berhasil diperbarui.');

            $submission->refresh()->load('attachments');

            $this->assertSame(2, $submission->attachments->count());
            $this->assertSame($storedSignature, $submission->approval_data[$approvalKey]['signature']);
        }
    }

    public function test_fixed_qc_draft_replaces_same_field_attachment_and_deletes_marked_attachment(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::with('attachments')->firstOrFail();
        $oldBefore = $submission->attachments->firstWhere('field_key', 'foto_before');
        $oldAfter = $submission->attachments->firstWhere('field_key', 'foto_after');
        $oldBeforePath = $oldBefore->file_path;

        $replacePayload = $payload;
        unset($replacePayload['attachments']);
        $replacePayload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->image('before-new.jpg', 20, 20)],
        ];

        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission), $replacePayload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission->refresh()->load('attachments');
        $newBefore = $submission->attachments->firstWhere('field_key', 'foto_before');

        $this->assertSame(2, $submission->attachments->count());
        $this->assertNotSame($oldBefore->id, $newBefore->id);
        $this->assertSame('before-new.jpg', $newBefore->original_name);
        $this->assertTrue($submission->attachments->contains('id', $oldAfter->id));
        Storage::disk('local')->assertMissing($oldBeforePath);

        $deletePayload = $payload;
        unset($deletePayload['attachments']);
        $deletePayload['remove_existing_attachments'] = [$oldAfter->id];

        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission), $deletePayload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission->refresh()->load('attachments');

        $this->assertSame(1, $submission->attachments->count());
        $this->assertFalse($submission->attachments->contains('id', $oldAfter->id));
        Storage::disk('local')->assertMissing($oldAfter->file_path);
    }

    public function test_fixed_qc_submit_requires_replacement_when_required_attachment_is_marked_for_removal(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::with('attachments')->firstOrFail();
        $before = $submission->attachments->firstWhere('field_key', 'foto_before');
        $submitPayload = $this->fixedGeneralPayload($template);
        unset($submitPayload['attachments']);
        $submitPayload['remove_existing_attachments'] = [$before->id];

        $this->actingAs($user)
            ->from(route('user.qc.submissions.edit', $submission))
            ->patch(route('user.qc.submissions.update', $submission), $submitPayload)
            ->assertRedirect(route('user.qc.submissions.edit', $submission))
            ->assertSessionHasErrors([
                'attachments.foto_before' => 'Foto Before wajib diupload. Dokumen Pendukung boleh dikosongkan.',
            ]);

        $this->assertSame(2, $submission->fresh()->attachments()->count());
    }

    public function test_user_editing_qc_submission_that_is_no_longer_draft_is_redirected_with_error(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::firstOrFail();
        $submission->forceFill([
            'status' => 'pending_approval',
            'submitted_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->get(route('user.qc.submissions.edit', $submission->fresh()))
            ->assertRedirect(route('user.qc.submissions.show', $submission->fresh()))
            ->assertSessionHasErrors(['submission']);
    }

    public function test_user_saving_stale_qc_draft_is_redirected_with_error_instead_of_forbidden(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::firstOrFail();
        $submission->forceFill([
            'status' => 'pending_approval',
            'submitted_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission->fresh()), $payload)
            ->assertRedirect(route('user.qc.submissions.show', $submission->fresh()))
            ->assertSessionHasErrors(['submission']);
    }

    public function test_user_cannot_submit_fixed_qc_without_final_check(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        unset($payload['body']['final_check']);

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors(['body.final_check' => 'Submit QC hanya bisa dilakukan jika Final Check sudah dicentang.']);

        $this->assertSame(0, QcFormSubmission::count());
    }

    public function test_user_can_submit_fixed_general_qc_and_open_pdf(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['approval']['qc_inspector_q_c_inspektor'] = [
            'name' => 'User QC',
            'date' => '2026-05-15',
            'role' => 'QC Inspektor',
            'signed_at' => now()->toISOString(),
            'signature' => '',
        ];
        $payload['approval_signature_files']['qc_inspector_q_c_inspektor'] = UploadedFile::fake()->image('qc-signature.png', 20, 10);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame('Template Fixed General', $submission->template->name);
        $this->assertTrue($submission->body_data['final_check']);
        $this->assertSame($user->name, $submission->general_info['inspector_qc']);
        $signature = $submission->approval_data['qc_inspector_q_c_inspektor']['signature'] ?? '';
        $this->assertStringStartsWith('/storage/signatures/qc/', $signature);
        Storage::disk('public')->assertExists(substr(parse_url($signature, PHP_URL_PATH), strlen('/storage/')));

        $submission->load('approvalFlow.steps');
        $submitterStep = $submission->approvalFlow->steps->firstWhere('is_submitter_signature', true);
        $this->assertNotNull($submitterStep);
        $this->assertStringStartsWith('signatures/approval/', $submitterStep->signature_path);
        $this->assertNull($submitterStep->signature_data);
        Storage::disk('public')->assertExists($submitterStep->signature_path);

        $pdfUrl = route('user.qc.submissions.pdf', $submission);
        $this->assertStringContainsString("/submissions/{$submission->id}/pdf", $pdfUrl);

        $this->actingAs($user)
            ->get($pdfUrl)
            ->assertOk();

        $legacyPdfUrl = route('user.qc.submissions.pdf', QcFormSubmission::routeKeyFromFormNumber($submission->form_number));

        $this->actingAs($user)
            ->get($legacyPdfUrl)
            ->assertOk();
    }

    public function test_qc_submitter_signature_storage_url_with_subpath_is_available_for_pdf(): void
    {
        Storage::fake('public');
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $relativeSignaturePath = 'signatures/qc/submission-subpath-signature.png';
        Storage::disk('public')->put($relativeSignaturePath, $this->validSignatureBinary());

        $signatureUrl = 'https://dept-pmms.com/ovh/storage/'.$relativeSignaturePath;
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '006/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'report_no' => '006/QC/05-2026',
            'pekerjaan' => 'Inspection',
            'general_info' => $this->fixedHeader(),
            'body_data' => [
                'final_check' => true,
                'general_rows' => [],
            ],
            'approval_data' => [
                'qc_inspector_q_c_inspektor' => [
                    'signature' => $signatureUrl,
                    'name' => 'User QC',
                    'date' => '2026-05-22',
                    'role' => 'QC Inspektor',
                    'signed_at' => now()->toISOString(),
                ],
            ],
        ]);

        app(ApprovalFlowService::class)->startForSubmission($submission, 'qc');

        $submitterStep = $submission->refresh()
            ->approvalFlow
            ->steps
            ->firstWhere('is_submitter_signature', true);

        $this->assertNotNull($submitterStep);
        $this->assertStringStartsWith('signatures/approval/', $submitterStep->signature_path);
        Storage::disk('public')->assertExists($submitterStep->signature_path);

        $submitterStep->forceFill(['signature_path' => null])->save();
        $submission->refresh()->load(['template.blocks', 'rows', 'attachments', 'user', 'approvalFlow.steps']);

        $html = view('pdf.qc-submission', [
            'submission' => $submission,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
        ])->render();

        $this->assertStringContainsString('<img src="data:image/png;base64,', $html);
        $this->assertStringContainsString('class="sig-img"', $html);
    }

    public function test_user_can_submit_fixed_general_qc_with_not_ok_status(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['body']['general_rows'][0]['status'] = 'Not Ok';
        $payload['body']['general_rows'][0]['catatan'] = 'Perlu tindak lanjut';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame('Not Ok', $submission->body_data['general_rows'][0]['status']);
        $this->assertSame('Perlu tindak lanjut', $submission->body_data['general_rows'][0]['catatan']);
        $this->assertTrue($submission->body_data['final_check']);
    }

    public function test_fixed_general_qc_requires_note_when_status_is_not_ok(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['body']['general_rows'][0]['status'] = 'Not Ok';
        $payload['body']['general_rows'][0]['catatan'] = '';

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors([
                'body.general_rows.0.catatan' => 'Catatan wajib diisi jika status Not Ok.',
            ]);

        $this->assertSame(0, QcFormSubmission::count());
    }

    public function test_fixed_electrical_not_ok_requires_remark_and_saves_all_sections(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $template = QcFormTemplate::create([
            'code' => 'QCR-ELECTRICAL-SUBMIT-001',
            'name' => 'Template QC Electrical',
            'category' => 'Electrical',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_ELECTRICAL,
            'body_schema' => FixedQcTemplate::defaultSchema(FixedQcTemplate::TYPE_ELECTRICAL),
        ]);

        $payload = [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => $this->fixedHeader(),
            'body' => [
                'final_check' => '1',
                'electrical_stator_rows' => collect($template->body_schema['stator_rows'])->map(fn () => ['value' => '100'])->all(),
                'electrical_rotor_rows' => collect($template->body_schema['rotor_rows'])->map(fn () => ['value' => '100'])->all(),
                'electrical_ovality_rows' => collect($template->body_schema['ovality_rows'])->map(fn () => ['tir' => '35'])->all(),
                'electrical_installation_rows' => collect($template->body_schema['installation_rows'])->map(fn () => ['status' => 'OK', 'remark' => ''])->all(),
                'electrical_uncouple_rows' => collect($template->body_schema['uncouple_rows'])->map(fn () => ['value_1' => '10'])->all(),
            ],
            'approval' => [
                'qc_inspector_q_c_inspektor' => [
                    'signature' => $this->validSignatureData(),
                    'name' => 'User QC',
                    'date' => '2026-06-08',
                    'role' => 'QC Inspektor',
                    'signed_at' => now()->toISOString(),
                ],
            ],
        ] + $this->requiredQcAttachments();

        $payload['body']['electrical_installation_rows'][0] = ['status' => 'NOT OK', 'remark' => ''];

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertSessionHasErrors([
                'body.electrical_installation_rows.0.remark' => 'Keterangan / Remarks wajib diisi jika status NOT OK.',
            ]);

        $payload['body']['electrical_installation_rows'][0]['remark'] = 'Terminasi perlu dikencangkan';
        $payload['attachments'] = $this->requiredQcAttachments()['attachments'];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('NOT OK', $submission->body_data['electrical_installation_rows'][0]['status']);
        $this->assertSame('Terminasi perlu dikencangkan', $submission->body_data['electrical_installation_rows'][0]['remark']);
        $this->assertSame(5, $submission->rows()->where('block_type', 'electrical_installation')->count());
        $this->assertSame(6, $submission->rows()->where('block_type', 'electrical_uncouple')->count());

        $this->actingAs($user)
            ->get(route('user.qc.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Pengukuran Ovality')
            ->assertSee('Terminasi perlu dikencangkan');

        $this->actingAs($user)
            ->get(route('user.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_user_can_submit_fixed_brics_qc_with_dynamic_manpower_rows(): void
    {
        [$user, $template] = $this->makeFixedBricsTemplate();
        $payload = $this->fixedBricsPayload($template);

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('placeholder="Contoh: Nama Vendor"', false)
            ->assertSee('placeholder="Contoh: Nama/Jabatan"', false);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame('SPV SHIFT', $submission->body_data['brics_manpower_rows'][0]['left_label']);
        $this->assertSame('Andi', $submission->body_data['brics_manpower_rows'][0]['left_value']);
        $this->assertSame('CUSTOM CREW', $submission->body_data['brics_manpower_rows'][2]['left_label']);
        $this->assertSame('Andi', $submission->body_data['brics_manpower']['spv_shift']);
        $this->assertSame('Vendor', $submission->approval_data['brics_vendor']['group']);
        $this->assertSame('Supplier PIC', $submission->approval_data['brics_vendor']['label']);
        $this->assertSame('Customer Supervisor', $submission->approval_data['brics_customer_supervisor']['group']);
        $this->assertSame('Support PIC', $submission->approval_data['brics_customer_supervisor']['label']);
        $this->assertSame('', $submission->approval_data['brics_approve_by']['label']);

        $submission->load(['template.blocks', 'rows', 'attachments', 'user', 'approvalFlow.steps']);
        $html = view('pdf.qc-submission', [
            'submission' => $submission,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
        ])->render();

        $this->assertStringContainsString('VENDOR', $html);
        $this->assertStringNotContainsString('SUPPLIER PARTNER', $html);
        $this->assertStringContainsString('SUPPLIER PIC', $html);
        $this->assertStringNotContainsString('<td>APPROVE BY</td>', $html);
    }

    public function test_fixed_brics_optional_sections_are_not_required_on_submit(): void
    {
        [$user, $template] = $this->makeFixedBricsTemplate();
        $payload = $this->fixedBricsPayload($template);
        unset(
            $payload['body']['brics_manpower_rows'],
            $payload['body']['brics_weather'],
            $payload['body']['brics_checks']
        );

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame('PT Test', $submission->body_data['brics_customer']['company_name']);
        $this->assertSame('54 m', $submission->body_data['brics_technical']['kiln_length']);
    }

    public function test_fixed_brics_required_customer_and_technical_fields_are_validated_on_submit(): void
    {
        [$user, $template] = $this->makeFixedBricsTemplate();
        $payload = $this->fixedBricsPayload($template);
        $payload['body']['brics_customer']['company_name'] = '';
        $payload['body']['brics_technical']['kiln_length'] = '';

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors([
                'body.brics_customer.company_name' => 'COMPANY NAME wajib diisi.',
                'body.brics_technical.kiln_length' => 'Kiln Length wajib diisi.',
            ]);

        $this->assertSame(0, QcFormSubmission::count());
    }

    public function test_user_can_submit_fixed_castable_qc_with_dynamic_monitoring_rows(): void
    {
        [$user, $template] = $this->makeFixedCastableTemplate();
        $payload = $this->fixedCastablePayload($template);

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Monitoring Installation Castable')
            ->assertSee('Tambah Row Monitoring')
            ->assertDontSee('QC SIGN / DATE')
            ->assertSee('placeholder="Contoh: Nama/Jabatan"', false);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame('pending_approval', $submission->status);
        $this->assertSame('Castable LC-16', $submission->body_data['castable_monitoring_type']);
        $this->assertSame('Catatan khusus monitoring', $submission->body_data['castable_monitoring_note']);
        $this->assertSame('Catatan form QC castable', $submission->note);
        $this->assertSame('120 x 80 x 40', $submission->body_data['castable_checks']['sample_dimention']['value']);
        $this->assertSame('120', $submission->body_data['castable_checks']['sample_dimention']['dimensions']['length']);
        $this->assertSame('6.5', $submission->body_data['castable_checks']['water_add']['value']);
        $this->assertSame('2026-05-15', $submission->body_data['castable_sample']['qc_date']);
        $this->assertArrayNotHasKey('qc_sign_date', $submission->body_data['castable_sample']);
        $this->assertCount(2, $submission->body_data['castable_monitoring_rows']);
        $this->assertSame('BATCH-02', $submission->body_data['castable_monitoring_rows'][1]['batch_number']);
        $this->assertSame('Supervisor A', $submission->body_data['castable_monitoring_signatures']['prepared_by']['name']);
        $this->assertSame('', $submission->body_data['castable_monitoring_signatures']['known_by']['name']);
        $this->assertSame(2, $submission->rows()->where('block_type', 'castable_monitoring')->count());
        $this->assertSame('Supervisor Approval', $submission->approval_data['castable_filled_by']['group']);
        $this->assertSame('*1 diisi', $submission->approval_data['castable_filled_by']['label']);
        $this->assertSame('Manager Approval', $submission->approval_data['castable_approved_1']['group']);

        $submission->load(['template.blocks', 'rows', 'attachments', 'user', 'approvalFlow.steps']);
        $html = view('pdf.qc-submission', [
            'submission' => $submission,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
        ])->render();

        $this->assertStringContainsString('Supervisor Approval', $html);
        $this->assertStringContainsString('*1 diisi', $html);
        $this->assertStringContainsString('QC DATE', $html);
        $this->assertStringContainsString('2026-05-15', $html);
        $this->assertStringNotContainsString('QC SIGN / DATE', $html);

        $this->actingAs($user)
            ->get(route('user.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_qc_attachment_must_be_jpg_or_png_and_is_served_through_authorized_route(): void
    {
        Storage::fake('local');
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $invalidPayload = $this->fixedGeneralPayload($template);
        $invalidPayload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->create('payload.svg', 1, 'image/svg+xml')],
        ];

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $invalidPayload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors('attachments.foto_before.0');

        $validPayload = $this->fixedGeneralPayload($template);
        $validPayload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->image('before.jpg')],
            'foto_after' => [UploadedFile::fake()->image('after.jpg')],
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $validPayload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $attachment = $submission->attachments()->firstOrFail();

        Storage::disk('local')->assertExists($attachment->file_path);
        $this->assertFalse(Storage::disk('public')->exists($attachment->file_path));

        $this->actingAs($user)
            ->get(route('user.qc.attachments.show', $attachment))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_deleting_qc_submission_removes_attachment_files(): void
    {
        Storage::fake('local');
        [$user, $template, $block, $row] = $this->makeActiveTemplate();
        $payload = $this->payload($template, $block, $row, 'submit');
        $payload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->image('before.jpg')],
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::with('attachments')->firstOrFail();
        $attachmentPath = $submission->attachments->firstOrFail()->file_path;

        Storage::disk('local')->assertExists($attachmentPath);

        $this->actingAs($user)
            ->delete(route('user.qc.submissions.destroy', $submission))
            ->assertRedirect(route('user.qc.history.index'))
            ->assertSessionHas('success', 'Form QC berhasil dihapus.');

        $this->assertSoftDeleted('qc_form_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('qc_form_submission_attachments', ['file_path' => $attachmentPath]);
        Storage::disk('local')->assertMissing($attachmentPath);
    }

    public function test_deleting_one_qc_submission_keeps_master_status_close_when_another_submitted_form_exists(): void
    {
        Storage::fake('local');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $master = $this->sharedQcMasterDataRecord();

        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['master_data_record_id'] = $master->id;

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['master_data_record_id'] = $master->id;

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submissions = QcFormSubmission::orderBy('id')->get();
        $this->assertCount(2, $submissions);
        $this->assertSame('close', $master->refresh()->inspection_status);

        $this->actingAs($user)
            ->delete(route('user.qc.submissions.destroy', $submissions->first()))
            ->assertRedirect(route('user.qc.history.index'));

        $this->assertSoftDeleted('qc_form_submissions', ['id' => $submissions->first()->id]);
        $this->assertSame('close', $master->refresh()->inspection_status);
    }

    public function test_deleting_one_qc_draft_keeps_master_status_ongoing_when_another_draft_exists(): void
    {
        Storage::fake('local');
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $master = $this->sharedQcMasterDataRecord();

        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';
        $payload['header']['master_data_record_id'] = $master->id;

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';
        $payload['header']['master_data_record_id'] = $master->id;

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submissions = QcFormSubmission::orderBy('id')->get();
        $this->assertCount(2, $submissions);
        $this->assertSame('ongoing', $master->refresh()->inspection_status);

        $this->actingAs($user)
            ->delete(route('user.qc.submissions.destroy', $submissions->first()))
            ->assertRedirect(route('user.qc.drafts.index'));

        $this->assertSoftDeleted('qc_form_submissions', ['id' => $submissions->first()->id]);
        $this->assertSame('ongoing', $master->refresh()->inspection_status);
    }

    public function test_admin_can_permanently_delete_qc_submission_with_related_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$user, $template] = $this->makeFixedGeneralTemplate();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $payload = $this->fixedGeneralPayload($template);
        $payload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->image('before.jpg')],
            'foto_after' => [UploadedFile::fake()->image('after.jpg')],
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::with(['attachments', 'approvalFlow.steps'])->firstOrFail();
        $attachmentPath = $submission->attachments->firstOrFail()->file_path;
        $approvalFlowId = $submission->approvalFlow->id;
        $approvalEventIds = $submission->approvalFlow->events()->pluck('id')->all();
        $approvalStepIds = $submission->approvalFlow->steps->pluck('id')->all();
        $signaturePath = $submission->approvalFlow->steps
            ->pluck('signature_path')
            ->filter()
            ->first();

        Storage::disk('local')->assertExists($attachmentPath);
        $this->assertNotNull($signaturePath);
        Storage::disk('public')->assertExists($signaturePath);

        $this->actingAs($admin)
            ->delete(route('admin.qc.submissions.destroy', $submission))
            ->assertRedirect()
            ->assertSessionHas('success', 'Submission QC berhasil dihapus permanen.');

        $this->assertDatabaseMissing('qc_form_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('qc_form_submission_attachments', ['file_path' => $attachmentPath]);
        $this->assertDatabaseMissing('approval_flows', ['id' => $approvalFlowId]);
        foreach ($approvalEventIds as $eventId) {
            $this->assertDatabaseMissing('approval_events', ['id' => $eventId]);
        }
        foreach ($approvalStepIds as $stepId) {
            $this->assertDatabaseMissing('approval_steps', ['id' => $stepId]);
        }
        Storage::disk('local')->assertMissing($attachmentPath);
        Storage::disk('public')->assertMissing($signaturePath);
    }

    public function test_user_can_submit_fixed_welding_qc_and_open_pdf(): void
    {
        [$user, $template] = $this->makeFixedWeldingTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedWeldingPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('pending_approval', $submission->status);
        $this->assertContains('Final Check', $submission->body_data['check_steps']);
        $this->assertSame($user->name, $submission->general_info['inspector_qc']);

        $this->actingAs($user)
            ->get(route('user.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_fixed_welding_qc_uses_only_admin_template_rows(): void
    {
        [$user, $template] = $this->makeFixedWeldingTemplate();
        $payload = $this->fixedWeldingPayload($template);
        $payload['body']['welder_rows'][] = [
            'no' => '99',
            'nama_welder' => 'Injected Welder',
            'posisi_pengelasan' => '9G',
            'diameter_electrode' => '9.9',
            'electrode_filter' => 'Injected',
            'amper' => '999',
            'keterangan' => 'Injected',
        ];
        $payload['body']['result_rows'][] = [
            'no' => '99',
            'deskripsi' => 'Injected Result',
            'status' => 'Baik',
            'keterangan' => 'Injected',
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertCount(1, $submission->body_data['welder_rows']);
        $this->assertCount(1, $submission->body_data['result_rows']);
        $this->assertSame('Visual welding', $submission->body_data['result_rows'][0]['deskripsi']);
        $this->assertSame(1, $submission->rows()->where('block_type', 'welding_welder')->count());
        $this->assertSame(1, $submission->rows()->where('block_type', 'welding_result')->count());
        $this->assertDatabaseMissing('qc_form_submission_rows', [
            'block_type' => 'welding_welder',
            'catatan' => 'Injected',
        ]);
    }

    private function makeActiveTemplate(): array
    {
        $user = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $template = QcFormTemplate::create([
            'code' => 'QCR-ACTIVE-SUBMIT-001',
            'name' => 'Template Aktif QC',
            'category' => 'Crusher',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
        ]);

        $block = $template->blocks()->create([
            'type' => 'checklist_table',
            'title' => 'Item Pengecekan',
            'order_no' => 1,
            'config' => [
                'columns' => [
                    ['key' => 'kategori', 'label' => 'Kategori', 'type' => 'text'],
                    ['key' => 'item', 'label' => 'Item Pengecekan', 'type' => 'text'],
                    ['key' => 'standar', 'label' => 'Standar', 'type' => 'text'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'radio', 'options' => ['OK', 'Not OK']],
                    ['key' => 'catatan', 'label' => 'Catatan', 'type' => 'textarea'],
                ],
            ],
        ]);

        $row = $block->tableRows()->create([
            'qc_form_template_id' => $template->id,
            'order_no' => 1,
            'row_data' => [
                'kategori' => 'Crusher',
                'item' => 'Hammer',
                'standar' => 'Tidak Retak',
            ],
        ]);

        return [$user, $template, $block, $row];
    }

    private function makeFixedGeneralTemplate(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $template = QcFormTemplate::create([
            'code' => 'QCR-FIXED-GENERAL-SUBMIT-001',
            'name' => 'Template Fixed General',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_GENERAL,
            'body_schema' => [
                'rows' => [
                    ['item_pengecekan' => 'Cek bearing', 'standar' => 'Normal', 'urutan' => 1],
                ],
            ],
        ]);

        return [$user, $template];
    }

    private function makeFixedWeldingTemplate(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $template = QcFormTemplate::create([
            'code' => 'QCR-FIXED-WELDING-SUBMIT-001',
            'name' => 'Template Fixed Welding',
            'category' => 'Welding',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_WELDING,
            'body_schema' => [
                'welder_rows' => [
                    ['no' => 1, 'nama_welder' => 'Welder A', 'posisi_pengelasan' => '1G', 'diameter_electrode' => '3.2', 'electrode_filter' => 'E7018', 'amper' => '90', 'keterangan' => ''],
                ],
                'result_rows' => [
                    ['no' => 1, 'deskripsi' => 'Visual welding', 'keterangan' => ''],
                ],
            ],
        ]);

        return [$user, $template];
    }

    private function makeFixedBricsTemplate(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $template = QcFormTemplate::create([
            'code' => 'QCR-FIXED-BRICS-SUBMIT-001',
            'name' => 'Template Fixed Brics',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_BRICS,
            'body_schema' => [
                'approval_defaults' => [],
            ],
        ]);

        return [$user, $template];
    }

    private function makeFixedCastableTemplate(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $template = QcFormTemplate::create([
            'code' => 'QCR-FIXED-CASTABLE-SUBMIT-001',
            'name' => 'Template Fixed Castable',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_CASTABLE,
            'body_schema' => [
                'approval_defaults' => [],
            ],
        ]);

        return [$user, $template];
    }

    private function fixedHeader(): array
    {
        return [
            'doc_number' => 'DOC-QC-001',
            'plant' => 'TONASA 4',
            'tag_num' => 'TAG-01',
            'functional_location' => 'FL-001',
            'id_equipment' => 'EQ-001',
            'name_equipment' => 'Bearing Conveyor',
            'area' => 'Crusher Area',
            'date_time' => '2026-05-08T10:00',
            'inspector_qc' => 'Manual Inspector',
            'pekerjaan' => 'Inspection',
            'unit_kerja' => 'Maintenance',
            'durasi' => '240',
        ];
    }

    private function fixedGeneralPayload(QcFormTemplate $template): array
    {
        return [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => $this->fixedHeader(),
            'body' => [
                'final_check' => '1',
                'general_rows' => [
                    [
                        'item_pengecekan' => 'Cek bearing',
                        'standar' => 'Normal',
                        'status' => 'Ok',
                        'catatan' => 'Aman',
                    ],
                ],
            ],
            'note' => 'Catatan fixed general',
            'approval' => [
                'qc_inspector_q_c_inspektor' => [
                    'signature' => $this->validSignatureData(),
                    'name' => 'User QC',
                    'date' => '2026-05-15',
                    'role' => 'QC Inspektor',
                    'signed_at' => now()->toISOString(),
                ],
            ],
        ] + $this->requiredQcAttachments();
    }

    private function sharedQcMasterDataRecord(): MasterDataRecord
    {
        return MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'FL-SHARED-QC',
            'equipment_no' => 'EQ-SHARED-QC',
            'section_no' => 'SEC-SHARED-QC',
            'description' => 'SHARED QC EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);
    }

    private function fixedWeldingPayload(QcFormTemplate $template): array
    {
        return [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => $this->fixedHeader(),
            'body' => [
                'methods' => ['Visual Check'],
                'check_steps' => ['1', 'Final Check'],
                'final_check' => '1',
                'welder_rows' => [
                    [
                        'no' => '1',
                        'nama_welder' => 'Welder A',
                        'posisi_pengelasan' => '1G',
                        'diameter_electrode' => '3.2',
                        'electrode_filter' => 'E7018',
                        'amper' => '90',
                        'keterangan' => 'OK',
                    ],
                ],
                'result_rows' => [
                    [
                        'no' => '1',
                        'deskripsi' => 'Visual welding',
                        'status' => 'Baik',
                        'keterangan' => 'Rapi',
                    ],
                ],
            ],
            'note' => 'Catatan fixed welding',
            'approval' => [
                'qc_inspector_q_c_inspektor' => [
                    'signature' => $this->validSignatureData(),
                    'name' => 'User QC',
                    'date' => '2026-05-15',
                    'role' => 'QC Inspektor',
                    'signed_at' => now()->toISOString(),
                ],
            ],
        ] + $this->requiredQcAttachments();
    }

    private function fixedBricsPayload(QcFormTemplate $template): array
    {
        $checks = collect(FixedQcTemplate::bricsInspectionSections())
            ->flatMap(fn ($section) => $section['items'])
            ->mapWithKeys(fn ($item) => [$item['key'] => [
                'status' => 'OK',
                'remark' => 'Aman',
            ]])
            ->all();

        return [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => [
                'tahun' => '2026',
                'area' => 'RAW MILL',
                'tag_num' => 'BR-01',
                'functional_location' => 'FL-BRICS',
                'name_equipment' => 'Kiln Bricks',
                'id_equipment' => 'EQ-BRICS',
            ],
            'body' => [
                'final_check' => '1',
                'brics_customer' => [
                    'company_name' => 'PT Test',
                    'subject' => 'BRICK INSTALLATIONS',
                    'locations' => 'ROTARY KILN PLANT',
                    'install_method' => 'CLENCH LINING',
                    'installations_section' => 'COOLING ZONE',
                ],
                'brics_technical' => [
                    'kiln_length' => '54 m',
                    'kiln_diameter' => '5.2 m',
                    'starting_metering' => '10 m',
                    'activity_date' => '2026-05-15',
                    'finishing_metering' => '18 m',
                    'start_finishing_ring' => 'R-10 / R-18',
                ],
                'brics_manpower_rows' => [
                    ['left_label' => 'SPV SHIFT', 'left_value' => 'Andi', 'right_label' => 'SAFETY', 'right_value' => 'Budi'],
                    ['left_label' => 'HELPER', 'left_value' => '4', 'right_label' => 'QC', 'right_value' => 'Rina'],
                    ['left_label' => 'CUSTOM CREW', 'left_value' => '2', 'right_label' => '', 'right_value' => ''],
                ],
                'brics_checks' => $checks,
            ],
            'approval' => [
                'brics_report_by' => [
                    'signature' => $this->validSignatureData(),
                    'name' => 'User QC',
                    'date' => '2026-05-15',
                    'role' => 'QC Inspektor',
                    'group' => 'Report by',
                    'label' => 'QC / SPV',
                    'signed_at' => now()->toISOString(),
                ],
                'brics_vendor' => [
                    'name' => 'Vendor A',
                    'group' => 'Supplier Partner',
                    'label' => 'Supplier PIC',
                    'role' => 'Supplier PIC',
                ],
                'brics_customer_supervisor' => [
                    'name' => 'Customer A',
                    'group' => 'Customer Support',
                    'label' => 'Support PIC',
                    'role' => 'Support PIC',
                ],
                'brics_name_unit' => [
                    'name' => 'Unit A',
                    'group' => 'Name Unit',
                    'label' => 'Unit Area',
                    'role' => 'Unit Area',
                ],
            ],
        ] + $this->requiredQcAttachments();
    }

    private function fixedCastablePayload(QcFormTemplate $template): array
    {
        $checks = collect(FixedQcTemplate::castableInspectionRows())
            ->mapWithKeys(function ($item) {
                $numberValues = [
                    'water_add' => '6.5',
                    'needle_add' => '2',
                    'mixing_time' => '15',
                    'thickness' => '50',
                    'no_of_layer' => '3',
                    'no_of_segment' => '8',
                    'segment_area' => '12.5',
                    'total_installation_time' => '120',
                    'quantity_used' => '250',
                ];

                $row = [
                    'status' => $item['options'][0] ?? '',
                    'value' => empty($item['options']) ? ($numberValues[$item['key']] ?? '1') : '',
                    'detail' => 'Detail '.$item['no'],
                ];

                if (($item['input'] ?? null) === 'dimension') {
                    $row['dimensions'] = [
                        'length' => '120',
                        'width' => '80',
                        'height' => '40',
                    ];
                    $row['value'] = '';
                }

                return [$item['key'] => $row];
            })
            ->all();

        return [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => [
                'plant' => 'TONASA 4',
                'tahun' => '2026',
                'area' => 'KILN',
                'date_time' => '2026-05-15T08:00',
                'tag_num' => 'CAST-01',
                'functional_location' => 'FL-CAST',
                'name_equipment' => 'Castable Kiln',
                'id_equipment' => 'EQ-CAST',
                'alat' => 'Kiln',
                'pekerjaan' => 'Overhaul',
                'unit_kerja' => 'QC',
                'durasi' => '60',
            ],
            'body' => [
                'final_check' => '1',
                'castable_customer' => [
                    'company' => 'PT Test',
                    'install_method' => 'Casting',
                ],
                'castable_checks' => $checks,
                'castable_sample' => [
                    'sample_mixing_no' => 'SM-01',
                    'batch_number' => 'BN-01',
                    'quantity' => '12',
                    'qc_name' => 'User QC',
                    'qc_date' => '2026-05-15',
                ],
                'castable_monitoring_type' => 'Castable LC-16',
                'castable_monitoring_note' => 'Catatan khusus monitoring',
                'castable_monitoring_rows' => [
                    [
                        'quantity' => '25',
                        'batch_number' => 'BATCH-01',
                        'material_temperature' => '32',
                        'room_temperature' => '30',
                        'mixing_time' => '4',
                        'water_percentage' => '6.5',
                        'water_ph' => '7',
                        'water_temperature' => '28',
                        'installation_location' => 'Burner area',
                        'remark' => 'Normal',
                    ],
                    [
                        'quantity' => '30',
                        'batch_number' => 'BATCH-02',
                        'material_temperature' => '33',
                        'room_temperature' => '31',
                        'mixing_time' => '5',
                        'water_percentage' => '6.7',
                        'water_ph' => '7',
                        'water_temperature' => '29',
                        'installation_location' => 'Kiln hood',
                        'remark' => 'OK',
                    ],
                ],
                'castable_monitoring_signatures' => [
                    'prepared_by' => [
                        'name' => 'Supervisor A',
                        'date' => '2026-05-15',
                        'signature' => 'data:image/png;base64,'.base64_encode('prepared'),
                    ],
                    'known_by' => [
                        'name' => 'Customer A',
                        'date' => '2026-05-15',
                        'signature' => 'data:image/png;base64,'.base64_encode('known'),
                    ],
                ],
            ],
            'note' => 'Catatan form QC castable',
            'approval' => [
                'castable_filled_by' => [
                    'signature' => $this->validSignatureData(),
                    'name' => 'User QC',
                    'date' => '2026-05-15',
                    'role' => 'QC Inspektor',
                    'group' => 'Supervisor Approval',
                    'label' => '*1 diperiksa',
                    'signed_at' => now()->toISOString(),
                ],
                'castable_approved_1' => [
                    'name' => 'Manager A',
                    'group' => 'Manager Approval',
                    'label' => '*2 accepted',
                    'role' => '*2 accepted',
                ],
                'castable_approved_2' => [
                    'name' => 'Owner A',
                    'group' => 'Owner Approval',
                    'label' => '*3 known',
                    'role' => '*3 known',
                ],
            ],
        ] + $this->requiredQcAttachments();
    }

    private function payload(QcFormTemplate $template, $block, $row, string $action): array
    {
        return [
            'template_id' => $template->id,
            'action' => $action,
            'general_info' => [
                'report_no' => '',
                'ovh_plant' => 'Tonasa 4',
                'tahun' => '2026',
                'unit' => 'Crusher Area',
                'alat' => 'Crusher Rotor',
                'tag_num' => 'CR-01',
                'tgl_mulai' => '2026-05-06',
                'pekerjaan' => 'Penggantian Hammer',
                'durasi' => '8 Jam',
            ],
            'rows' => [
                $block->id => [
                    $row->id => [
                        'kategori' => 'Crusher',
                        'item' => 'Hammer',
                        'standar' => 'Tidak Retak',
                        'status_value' => 'OK',
                        'catatan' => 'Normal',
                    ],
                ],
            ],
            'note' => 'Catatan umum QC',
            'approval' => [
                'tanggal' => '2026-05-06',
                'diisi' => [
                    'signature' => 'data:image/png;base64,'.base64_encode('signature'),
                    'name' => 'User QC',
                    'role' => 'Quality Control Personil',
                    'signed_at' => now()->toISOString(),
                ],
            ],
        ];
    }

    private function tokenFromUrl(string $url): string
    {
        return basename((string) parse_url($url, PHP_URL_PATH));
    }

    private function validSignatureData(): string
    {
        return 'data:image/png;base64,'.base64_encode($this->validSignatureBinary());
    }

    private function requiredQcAttachments(): array
    {
        return [
            'attachments' => [
                'foto_before' => [UploadedFile::fake()->image('before.jpg', 20, 20)],
                'foto_after' => [UploadedFile::fake()->image('after.jpg', 20, 20)],
            ],
        ];
    }

    private function validSignatureBinary(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );
    }
}
