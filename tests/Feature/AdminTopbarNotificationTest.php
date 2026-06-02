<?php

namespace Tests\Feature;

use App\Models\AdminSubmissionNotificationRead;
use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use App\Support\AdminTopbarNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTopbarNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_topbar_shows_submitted_qc_and_commissioning_notifications(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create(['name' => 'User Inspector']);
        $qcTemplate = QcFormTemplate::create([
            'code' => 'QC-NOTIF',
            'name' => 'QC Notification',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);
        $commissioningTemplate = CommissioningFormTemplate::create([
            'code' => 'COM-NOTIF',
            'name' => 'Commissioning Notification',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);

        QcFormSubmission::create([
            'qc_form_template_id' => $qcTemplate->id,
            'user_id' => $user->id,
            'form_number' => '001/QC/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'equipment' => 'Kiln Drive',
            'area' => 'Kiln',
            'general_info' => ['master_data_auto_activated' => true],
        ]);

        CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $commissioningTemplate->id,
            'user_id' => $user->id,
            'form_number' => '002/COM/05-2026',
            'status' => 'pending_approval',
            'submitted_at' => now()->subMinute(),
            'equipment' => 'Motor Fan',
            'area' => 'Raw Mill',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('2 baru')
            ->assertSee('001/QC/05-2026')
            ->assertSee('002/COM/05-2026')
            ->assertSee('User Inspector sudah membuat form QC. Equipment otomatis diaktifkan.')
            ->assertSee('User Inspector sudah membuat form Commissioning.')
            ->assertSee('CM')
            ->assertSee(route('admin.notifications.qc.open', QcFormSubmission::first()), false)
            ->assertSee(route('admin.notifications.commissioning.open', CommissioningFormSubmission::first()), false);
    }

    public function test_admin_can_mark_all_topbar_notifications_as_read(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create(['name' => 'User Inspector']);
        $template = QcFormTemplate::create([
            'code' => 'QC-NOTIF',
            'name' => 'QC Notification',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '003/QC/06-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'equipment' => 'Kiln Drive',
            'area' => 'Kiln',
            'general_info' => [],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 baru')
            ->assertSee('003/QC/06-2026');

        $this->actingAs($admin)
            ->post(route('admin.notifications.read-all'))
            ->assertRedirect();

        $this->assertDatabaseHas('admin_submission_notification_reads', [
            'user_id' => $admin->id,
            'submission_type' => QcFormSubmission::class,
            'submission_id' => $submission->id,
            'status' => 'pending_approval',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('topbar-notification-badge', false)
            ->assertSee('0 baru')
            ->assertDontSee('003/QC/06-2026');
    }

    public function test_opening_admin_notification_marks_single_submission_as_read(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create();
        $template = CommissioningFormTemplate::create([
            'code' => 'COM-NOTIF',
            'name' => 'Commissioning Notification',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);
        $submission = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '004/COM/06-2026',
            'status' => 'pending_approval',
            'submitted_at' => now(),
            'equipment' => 'Motor Fan',
            'area' => 'Raw Mill',
            'header_data' => [],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications.commissioning.open', $submission))
            ->assertRedirect(route('admin.commissioning.submissions.pdf', $submission));

        $this->assertDatabaseHas('admin_submission_notification_reads', [
            'user_id' => $admin->id,
            'submission_type' => CommissioningFormSubmission::class,
            'submission_id' => $submission->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_admin_notification_reappears_when_submission_event_changes(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create();
        $template = QcFormTemplate::create([
            'code' => 'QC-NOTIF',
            'name' => 'QC Notification',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'body_schema' => [],
        ]);
        $submission = QcFormSubmission::create([
            'qc_form_template_id' => $template->id,
            'user_id' => $user->id,
            'form_number' => '005/QC/06-2026',
            'status' => 'draft',
            'equipment' => 'Raw Mill',
            'area' => 'Raw Mill',
            'general_info' => [],
        ]);

        AdminSubmissionNotificationRead::create([
            'user_id' => $admin->id,
            'submission_type' => QcFormSubmission::class,
            'submission_id' => $submission->id,
            'status' => 'draft',
            'notification_key' => AdminTopbarNotifications::notificationKey($submission),
            'read_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('0 baru')
            ->assertDontSee('005/QC/06-2026');

        $submission->timestamps = false;
        $submission->forceFill([
            'equipment' => 'Raw Mill Updated',
            'updated_at' => now()->addMinute(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 baru')
            ->assertSee('005/QC/06-2026');
    }
}
