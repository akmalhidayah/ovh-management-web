<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserRolePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_qc_pages_render_for_qc_user(): void
    {
        $user = User::factory()->make([
            'name' => 'User QC',
            'email' => 'qc@ovh.test',
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($user);

        foreach ([
            'user.qc.dashboard',
            'user.qc.forms.create',
            'user.qc.drafts.index',
            'user.qc.history.index',
            'user.qc.profile',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('user.qc.documents.index'))->assertRedirect(route('user.qc.history.index'));
    }

    public function test_other_roles_render_their_own_pages(): void
    {
        $roles = [
            'commissioning' => [
                'routes' => [
                    'user.commissioning.dashboard',
                    'user.commissioning.forms.create',
                    'user.commissioning.drafts.index',
                    'user.commissioning.history.index',
                    'user.commissioning.profile',
                ],
                'redirects' => [
                    'user.commissioning.documents.index' => 'user.commissioning.history.index',
                ],
            ],
            'pgo' => [
                'routes' => [
                    'user.pgo.dashboard',
                    'user.pgo.tasks.index',
                    'user.pgo.monitoring.index',
                    'user.pgo.documents.index',
                    'user.pgo.history.index',
                    'user.pgo.profile',
                ],
            ],
            'approval' => [
                'routes' => [
                    'user.approval.dashboard',
                    'user.approval.pending.index',
                    'user.approval.review.index',
                    'user.approval.history.index',
                    'user.approval.documents.index',
                    'user.approval.profile',
                ],
            ],
        ];

        foreach ($roles as $role => $config) {
            $user = User::factory()->make([
                'email' => "{$role}@ovh.test",
                'usertype' => 'user',
                'role' => $role,
            ]);

            $this->actingAs($user);

            foreach ($config['routes'] as $route) {
                $this->get(route($route))->assertOk();
            }

            foreach (($config['redirects'] ?? []) as $route => $target) {
                $this->get(route($route))->assertRedirect(route($target));
            }
        }
    }

    public function test_user_is_redirected_to_own_dashboard_when_opening_other_role_route(): void
    {
        $qcUser = User::factory()->make([
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($qcUser);

        $this->get(route('user.commissioning.dashboard'))->assertRedirect(route('user.qc.dashboard'));
        $this->get(route('user.pgo.dashboard'))->assertRedirect(route('user.qc.dashboard'));
        $this->get(route('user.approval.dashboard'))->assertRedirect(route('user.qc.dashboard'));
    }

    public function test_qc_dashboard_uses_real_submission_data_for_current_user(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $otherUser = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $template = QcFormTemplate::create([
            'code' => 'QC-DASH-001',
            'name' => 'Template Dashboard QC',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
        ]);
        $draftMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-QC-DASH-001',
            'equipment_no' => 'EQ-QC-DASH-001',
            'section_no' => 'SEC-QC-DASH-001',
            'description' => 'Real Pump P-101',
            'plant' => 'Tonasa 4',
            'area' => 'Raw Mill',
            'status' => 'active',
        ]);
        $pendingMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-QC-DASH-002',
            'equipment_no' => 'EQ-QC-DASH-002',
            'section_no' => 'SEC-QC-DASH-002',
            'description' => 'Real Conveyor CV-02',
            'plant' => 'Tonasa 5',
            'area' => 'Coal Mill',
            'status' => 'active',
        ]);
        $availableMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-QC-DASH-003',
            'equipment_no' => 'EQ-QC-DASH-003',
            'section_no' => 'SEC-QC-DASH-003',
            'description' => 'Available QC Equipment',
            'plant' => 'Tonasa 6',
            'area' => 'Crusher',
            'status' => 'active',
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '001/QC/05-2026',
            'status' => 'draft',
            'equipment' => 'Real Pump P-101',
            'plant' => 'Tonasa 4',
            'area' => 'Raw Mill',
            'general_info' => ['master_data_record_id' => $draftMaster->id],
            'updated_at' => Carbon::parse('2026-05-20 09:00'),
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '002/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => Carbon::parse('2026-05-20 10:00'),
            'equipment' => 'Real Conveyor CV-02',
            'plant' => 'Tonasa 5',
            'area' => 'Coal Mill',
            'template_name' => 'QC Conveyor Real',
            'general_info' => ['master_data_record_id' => $pendingMaster->id],
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $otherUser->id,
            'form_number' => '003/QC/05-2026',
            'status' => 'draft',
            'equipment' => 'Other User Crusher',
            'plant' => 'Tonasa 6',
            'area' => 'Crusher',
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('Daftar Equipment QC')
            ->assertSee('Semua Plant')
            ->assertSee('Semua Area')
            ->assertSee('Semua Status')
            ->assertSee('Real Pump P-101')
            ->assertSee('Real Conveyor CV-02')
            ->assertSee('Available QC Equipment')
            ->assertSee('On Going')
            ->assertSee('Complete')
            ->assertSee('Belum QC')
            ->assertSee('Buat QC')
            ->assertDontSee('Buat Manual')
            ->assertSee(e(route('user.qc.forms.create', [
                'master_data_record_id' => $availableMaster->id,
                'area' => $availableMaster->area,
            ])), false)
            ->assertDontSee('Other User Crusher')
            ->assertDontSee('QC - Gearbox GB-301');
    }

    public function test_commissioning_dashboard_uses_real_submission_data_for_current_user(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $otherUser = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-DASH-001',
            'name' => 'Template Dashboard Commissioning',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
        ]);
        $revisionMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-DASH-001',
            'equipment_no' => 'EQ-COM-DASH-001',
            'section_no' => 'SEC-COM-DASH-001',
            'description' => 'Real ID Fan IF-10',
            'plant' => 'Tonasa 4',
            'area' => 'Kiln',
            'status' => 'active',
        ]);
        $approvedMaster = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COM-DASH-002',
            'equipment_no' => 'EQ-COM-DASH-002',
            'section_no' => 'SEC-COM-DASH-002',
            'description' => 'Real Motor M-210',
            'plant' => 'Tonasa 5',
            'area' => 'Packing Plant',
            'status' => 'active',
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '001/COM/05-2026',
            'status' => 'revision_required',
            'equipment' => 'Real ID Fan IF-10',
            'area' => 'Kiln',
            'header_data' => ['plant' => 'Tonasa 4', 'master_data_record_id' => $revisionMaster->id],
            'updated_at' => Carbon::parse('2026-05-20 09:00'),
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '002/COM/05-2026',
            'status' => 'approved',
            'submitted_at' => Carbon::parse('2026-05-20 10:00'),
            'equipment' => 'Real Motor M-210',
            'area' => 'Packing Plant',
            'template_name' => 'Commissioning Motor Real',
            'header_data' => ['plant' => 'Tonasa 5', 'master_data_record_id' => $approvedMaster->id],
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $otherUser->id,
            'form_number' => '003/COM/05-2026',
            'status' => 'draft',
            'equipment' => 'Other User Cooler',
            'area' => 'Utilities',
        ]);

        $this->actingAs($user)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('Daftar Equipment Commissioning')
            ->assertSee('Real ID Fan IF-10')
            ->assertSee('Real Motor M-210')
            ->assertSee('Perlu Revisi')
            ->assertSee('Complete')
            ->assertDontSee('Buat Manual')
            ->assertDontSee('Other User Cooler')
            ->assertDontSee('Commissioning - ID Fan IF-02');
    }

    public function test_legacy_inspector_routes_redirect_to_qc_pages(): void
    {
        $this->get('/inspector/dashboard')->assertRedirect('/user/qc/dashboard');
        $this->get('/inspector/forms/create')->assertRedirect('/user/qc/forms/create');
        $this->get('/inspector/documents')->assertRedirect('/user/qc/history');
    }
}
