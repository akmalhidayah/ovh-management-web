<?php

namespace Tests\Feature;

use App\Models\CommissioningFormSubmission;
use App\Models\CommissioningFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use App\Support\PublicRegistrationAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_user_panel(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        User::factory()->create([
            'name' => 'QC Operator',
            'email' => 'qc.operator@ovh.test',
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.user-panel'))
            ->assertOk()
            ->assertSee('Userpanel')
            ->assertSee('QC Operator')
            ->assertSee('Quality Control')
            ->assertSee('Register Aktif');
    }

    public function test_admin_can_create_user_with_default_password(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.user-panel.store'), [
                'name' => 'Commissioning Baru',
                'email' => 'commissioning.baru@ovh.test',
                'phone' => '08123456789',
                'usertype' => 'user',
                'role' => 'commissioning',
            ])
            ->assertRedirect(route('admin.user-panel'))
            ->assertSessionHas('success');

        $user = User::where('email', 'commissioning.baru@ovh.test')->firstOrFail();

        $this->assertSame('user', $user->usertype);
        $this->assertSame('commissioning', $user->role);
        $this->assertTrue(Hash::check('overhaul123', $user->password));
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create([
            'password' => Hash::make('existing-password'),
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.user-panel.update', $user), [
                'name' => 'PGO Updated',
                'email' => 'pgo.updated@ovh.test',
                'phone' => '0800000000',
                'usertype' => 'user',
                'role' => 'pgo',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('PGO Updated', $user->name);
        $this->assertSame('pgo', $user->role);
        $this->assertTrue(Hash::check('existing-password', $user->password));
    }

    public function test_admin_can_update_qc_user_work_areas(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
            'profile_areas' => ['OLD AREA'],
        ]);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-ADMIN-AREA-001',
            'equipment_no' => 'EQ-ADMIN-AREA-001',
            'section_no' => 'SEC-ADMIN-AREA-001',
            'description' => 'Admin Area Equipment',
            'plant' => 'TONASA ADMIN',
            'area' => 'ADMIN AREA',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.user-panel.update', $user), [
                'name' => 'QC Area Updated',
                'email' => 'qc.area.updated@ovh.test',
                'phone' => '0800000001',
                'usertype' => 'user',
                'role' => 'qc',
                'profile_areas' => ['ADMIN AREA'],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame(['ADMIN AREA'], $user->profile_areas);
        $this->assertSame(['TONASA ADMIN'], $user->profile_plants);
    }

    public function test_admin_cannot_demote_own_account_to_user(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.user-panel.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'usertype' => 'user',
                'role' => 'qc',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $admin->refresh();

        $this->assertSame('admin', $admin->usertype);
        $this->assertSame('admin', $admin->role);
    }

    public function test_admin_can_delete_other_user_account(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create([
            'name' => 'User Hapus',
            'email' => 'hapus@ovh.test',
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.user-panel.destroy', $user))
            ->assertRedirect()
            ->assertSessionHas('success', 'Akun User Hapus berhasil dihapus.');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_delete_user_cleans_their_draft_submissions(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);
        $qcTemplate = QcFormTemplate::create([
            'code' => 'QC-DELETE-USER',
            'name' => 'Template Delete User QC',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
        ]);
        $commissioningTemplate = CommissioningFormTemplate::create([
            'code' => 'COM-DELETE-USER',
            'name' => 'Template Delete User Commissioning',
            'category' => 'Commissioning',
            'version' => '1.0',
            'status' => 'active',
        ]);
        $qcDraft = QcFormSubmission::create([
            'qc_form_template_id' => $qcTemplate->id,
            'user_id' => $user->id,
            'form_number' => '088/QC/05-2026',
            'status' => 'draft',
        ]);
        $commissioningDraft = CommissioningFormSubmission::create([
            'commissioning_form_template_id' => $commissioningTemplate->id,
            'user_id' => $user->id,
            'form_number' => '088/COM/05-2026',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.user-panel.destroy', $user))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('qc_form_submissions', ['id' => $qcDraft->id]);
        $this->assertDatabaseMissing('commissioning_form_submissions', ['id' => $commissioningDraft->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.user-panel.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_can_toggle_public_registration_access(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->assertTrue(PublicRegistrationAccess::enabled());

        $this->actingAs($admin)
            ->patch(route('admin.user-panel.registration-access'), [
                'enabled' => false,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Registrasi publik berhasil dinonaktifkan.');

        $this->assertFalse(PublicRegistrationAccess::enabled());
        auth()->logout();
        $this->get('/register')->assertNotFound();
        $this->get('/')->assertRedirect(route('login'));

        $this->actingAs($admin)
            ->patch(route('admin.user-panel.registration-access'), [
                'enabled' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Registrasi publik berhasil diaktifkan.');

        $this->assertTrue(PublicRegistrationAccess::enabled());
        auth()->logout();
        $this->get('/register')->assertOk();
    }
}
