<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
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
            ->assertSee(route('admin.qc.submissions.pdf', QcFormSubmission::first()), false)
            ->assertSee(route('admin.commissioning.submissions.pdf', CommissioningFormSubmission::first()), false);
    }
}
