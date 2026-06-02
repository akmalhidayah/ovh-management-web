<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiAccessModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_access_user_is_prompted_after_login_and_can_choose_modes(): void
    {
        $user = User::factory()->create([
            'email' => 'multi-access@ovh.test',
            'password' => Hash::make('secret123'),
            'usertype' => 'user',
            'role' => 'commissioning',
            'secondary_role' => 'approval',
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'multi-access@ovh.test',
            'password' => 'secret123',
        ])
            ->assertRedirect(route('access.choose'))
            ->assertSessionMissing('active_access_mode');

        $this->actingAs($user)
            ->get(route('access.choose'))
            ->assertOk()
            ->assertSee('User Commissioning')
            ->assertSee('Admin Monitoring');

        $this->actingAs($user)
            ->post(route('access.choose.store'), ['mode' => 'user:commissioning'])
            ->assertRedirect(route('user.commissioning.dashboard'))
            ->assertSessionHas('active_access_mode', 'user')
            ->assertSessionHas('active_user_role', 'commissioning');

        $this->actingAs($user)
            ->post(route('access.switch'), ['mode' => 'admin'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('active_access_mode', 'admin');
    }

    public function test_admin_can_grant_user_admin_monitoring_access_without_changing_operational_role(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create([
            'name' => 'QC Multi Access',
            'email' => 'qc.multi@ovh.test',
            'usertype' => 'user',
            'role' => 'qc',
            'secondary_role' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.user-panel.update', $user), [
                'name' => 'QC Multi Access',
                'email' => 'qc.multi@ovh.test',
                'phone' => '0800000002',
                'usertype' => 'user',
                'role' => 'qc',
                'secondary_role' => 'approval',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('user', $user->usertype);
        $this->assertSame('qc', $user->role);
        $this->assertSame('approval', $user->secondary_role);
    }

    public function test_user_can_have_qc_and_commissioning_access_with_secondary_role(): void
    {
        $user = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
            'secondary_role' => 'commissioning',
        ]);

        $this->actingAs($user)
            ->get(route('access.choose'))
            ->assertOk()
            ->assertSee('User Quality Control')
            ->assertSee('User Commissioning')
            ->assertDontSee('Admin Monitoring');

        $this->actingAs($user)
            ->post(route('access.choose.store'), ['mode' => 'user:commissioning'])
            ->assertRedirect(route('user.commissioning.dashboard'))
            ->assertSessionHas('active_access_mode', 'user')
            ->assertSessionHas('active_user_role', 'commissioning');

        $this->actingAs($user)
            ->withSession(['active_access_mode' => 'user', 'active_user_role' => 'commissioning'])
            ->get(route('user.commissioning.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->withSession(['active_access_mode' => 'user', 'active_user_role' => 'commissioning'])
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSessionHas('active_user_role', 'qc');
    }

    public function test_multi_access_approval_can_monitor_admin_qc_but_cannot_restore_or_delete(): void
    {
        $approvalUser = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
            'secondary_role' => 'approval',
        ]);
        $submission = $this->pendingQcSubmission();

        $this->actingAs($approvalUser)
            ->withSession(['active_access_mode' => 'admin'])
            ->get(route('admin.qc'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertDontSee('data-admin-delete-submission-form', false)
            ->assertDontSee('data-inspection-status-select', false);

        $this->actingAs($approvalUser)
            ->withSession(['active_access_mode' => 'admin'])
            ->delete(route('admin.qc.submissions.destroy', $submission))
            ->assertForbidden();

        $this->actingAs($approvalUser)
            ->withSession(['active_access_mode' => 'admin'])
            ->patch(route('admin.qc.submissions.restore-draft', $submission))
            ->assertForbidden();
    }

    public function test_multi_access_approval_can_generate_active_approval_link_from_admin_monitoring(): void
    {
        $approvalUser = User::factory()->create([
            'usertype' => 'user',
            'role' => 'commissioning',
            'secondary_role' => 'approval',
        ]);
        $submission = $this->pendingQcSubmission();
        $flow = $submission->approvalFlow()->create([
            'status' => ApprovalFlow::STATUS_PENDING,
            'current_step_order' => 1,
        ]);
        $flow->steps()->create([
            'step_order' => 1,
            'label' => 'Coordinator QC & Commissioning',
            'status' => ApprovalStep::STATUS_ACTIVE,
            'requires_magic_link' => true,
        ]);

        $response = $this->actingAs($approvalUser)
            ->withSession(['active_access_mode' => 'admin'])
            ->postJson(route('admin.qc.submissions.approval-link', $submission));

        $response
            ->assertOk()
            ->assertJsonStructure(['url']);

        $this->assertStringContainsString('/approval/', $response->json('url'));
    }

    private function pendingQcSubmission(): QcFormSubmission
    {
        $template = QcFormTemplate::create([
            'code' => 'QC-MULTI-ACCESS',
            'name' => 'QC Multi Access Template',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);

        return QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'form_number' => '099/QC/06-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'year' => '2026',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'equipment' => 'QC Multi Access Equipment',
            'general_info' => [
                'plant' => 'TONASA 4',
                'area' => 'RAW MILL',
                'name_equipment' => 'QC Multi Access Equipment',
            ],
        ]);
    }
}
