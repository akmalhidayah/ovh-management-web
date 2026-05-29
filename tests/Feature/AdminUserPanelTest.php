<?php

namespace Tests\Feature;

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
        $this->get('/')->assertOk()->assertDontSee(route('register'), false);

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
