<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTopbarNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_qc_user_topbar_shows_active_qc_master_data_notifications(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $record = $this->masterRecord(MasterDataRecord::CATEGORY_QC, 'QC Pump');

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('1 belum dibaca dari 1 notifikasi')
            ->assertSee('QC Pump')
            ->assertSee('Equipment baru aktif untuk dibuat form QC.')
            ->assertSee(e(route('user.qc.notifications.open', $record)), false);
    }

    public function test_commissioning_user_topbar_shows_active_commissioning_master_data_notifications(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $record = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'COM Motor');

        $this->actingAs($user)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('1 belum dibaca dari 1 notifikasi')
            ->assertSee('COM Motor')
            ->assertSee('Equipment baru aktif untuk dibuat form Commissioning.')
            ->assertSee(e(route('user.commissioning.notifications.open', $record)), false);
    }

    public function test_qc_user_can_mark_topbar_notifications_as_read(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $record = $this->masterRecord(MasterDataRecord::CATEGORY_QC, 'Read Pump');

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('user-notification-badge', false)
            ->assertSee('Baru');

        $this->actingAs($user)
            ->post(route('user.qc.notifications.read-all'))
            ->assertRedirect();

        $this->assertDatabaseHas('user_notification_reads', [
            'user_id' => $user->id,
            'master_data_record_id' => $record->id,
            'role' => 'qc',
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertDontSee('user-notification-badge', false)
            ->assertSee('0 belum dibaca dari 1 notifikasi')
            ->assertSee('Dibaca');
    }

    public function test_qc_user_topbar_shows_only_their_own_approved_submission_notification(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $otherUser = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $template = $this->qcTemplate();
        $ownSubmission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '777/QC/05-2026',
            'status' => 'approved',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Own Pump',
            'general_info' => [],
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $otherUser->id,
            'form_number' => '778/QC/05-2026',
            'status' => 'approved',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Other Pump',
            'general_info' => [],
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('Submission QC disetujui')
            ->assertSee('Form 777/QC/05-2026 sudah disetujui final.')
            ->assertSee(e(route('user.qc.notifications.submissions.open', $ownSubmission)), false)
            ->assertDontSee('778/QC/05-2026');
    }

    public function test_commissioning_user_topbar_shows_only_their_own_rejected_submission_notification(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $otherUser = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $template = $this->commissioningTemplate();
        $ownSubmission = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '777/COM/05-2026',
            'status' => 'revision_required',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Own Motor',
            'header_data' => [],
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $otherUser->id,
            'form_number' => '778/COM/05-2026',
            'status' => 'revision_required',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Other Motor',
            'header_data' => [],
        ]);

        $this->actingAs($user)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('Submission Commissioning ditolak')
            ->assertSee('Form 777/COM/05-2026 ditolak approval dan perlu diperbaiki.')
            ->assertSee(e(route('user.commissioning.notifications.submissions.open', $ownSubmission)), false)
            ->assertDontSee('778/COM/05-2026');
    }

    public function test_opening_submission_notification_marks_it_as_read(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $template = $this->qcTemplate();
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '779/QC/05-2026',
            'status' => 'approved',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Read Pump',
            'general_info' => [],
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.notifications.submissions.open', $submission))
            ->assertRedirect(route('user.qc.submissions.show', $submission));

        $this->assertDatabaseHas('user_submission_notification_reads', [
            'user_id' => $user->id,
            'role' => 'qc',
            'submission_type' => QcFormSubmission::class,
            'submission_id' => $submission->id,
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('0 belum dibaca dari 1 notifikasi')
            ->assertSee('Dibaca');
    }

    public function test_read_all_marks_submission_notifications_as_read(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $template = $this->commissioningTemplate();
        $submission = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '779/COM/05-2026',
            'status' => 'revision_required',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Read Motor',
            'header_data' => [],
        ]);

        $this->actingAs($user)
            ->post(route('user.commissioning.notifications.read-all'))
            ->assertRedirect();

        $this->assertDatabaseHas('user_submission_notification_reads', [
            'user_id' => $user->id,
            'role' => 'commissioning',
            'submission_type' => CommissioningFormSubmission::class,
            'submission_id' => $submission->id,
            'status' => 'revision_required',
        ]);

        $this->actingAs($user)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('0 belum dibaca dari 1 notifikasi')
            ->assertSee('Dibaca');
    }

    public function test_qc_user_topbar_shows_when_admin_restores_submission_to_draft(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $template = $this->qcTemplate();
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '780/QC/05-2026',
            'status' => 'approved',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Restored Pump',
            'general_info' => [],
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '781/QC/05-2026',
            'status' => 'draft',
            'equipment' => 'Normal Draft Pump',
            'general_info' => [],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.qc.submissions.restore-draft', $submission))
            ->assertRedirect();

        $submission->refresh();

        $this->actingAs($user)
            ->get(route('user.qc.dashboard'))
            ->assertOk()
            ->assertSee('Submission QC dikembalikan ke draft')
            ->assertSee('Form 780/QC/05-2026 dikembalikan admin ke draft dan bisa diedit lagi.')
            ->assertSee(e(route('user.qc.notifications.submissions.open', $submission)), false)
            ->assertDontSee('781/QC/05-2026');
    }

    public function test_commissioning_user_topbar_shows_when_admin_restores_submission_to_draft(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $template = $this->commissioningTemplate();
        $submission = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '780/COM/05-2026',
            'status' => 'approved',
            'submitted_at' => now()->subHour(),
            'equipment' => 'Restored Motor',
            'header_data' => [],
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.commissioning.submissions.restore-draft', $submission))
            ->assertRedirect();

        $submission->refresh();

        $this->actingAs($user)
            ->get(route('user.commissioning.dashboard'))
            ->assertOk()
            ->assertSee('Submission Commissioning dikembalikan ke draft')
            ->assertSee('Form 780/COM/05-2026 dikembalikan admin ke draft dan bisa diedit lagi.')
            ->assertSee(e(route('user.commissioning.notifications.submissions.open', $submission)), false);
    }

    public function test_opening_notification_marks_single_equipment_as_read(): void
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'commissioning']);
        $record = $this->masterRecord(MasterDataRecord::CATEGORY_COMMISSIONING, 'Read Motor');

        $this->actingAs($user)
            ->get(route('user.commissioning.notifications.open', $record))
            ->assertRedirect(route('user.commissioning.forms.create', [
                'master_data_record_id' => $record->id,
                'area' => $record->area,
            ]));

        $this->assertDatabaseHas('user_notification_reads', [
            'user_id' => $user->id,
            'master_data_record_id' => $record->id,
            'role' => 'commissioning',
        ]);
    }

    private function masterRecord(string $category, string $description): MasterDataRecord
    {
        return MasterDataRecord::create([
            'document_category' => $category,
            'year' => '2026',
            'func_location' => 'LOC-'.$description,
            'equipment_no' => 'EQ-'.$description,
            'section_no' => 'SEC-'.$description,
            'description' => $description,
            'plant' => 'TONASA 4',
            'area' => 'KILN',
            'status' => 'active',
        ]);
    }

    private function qcTemplate(): QcFormTemplate
    {
        return QcFormTemplate::create([
            'code' => 'QC-USER-NOTIF',
            'name' => 'QC User Notification',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);
    }

    private function commissioningTemplate(): CommissioningFormTemplate
    {
        return CommissioningFormTemplate::create([
            'code' => 'COM-USER-NOTIF',
            'name' => 'Commissioning User Notification',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);
    }
}
