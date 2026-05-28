<?php

namespace Tests\Feature;

use App\Models\OrganizationSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrganizationSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_organization_sections(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.organization-sections.index'))
            ->assertOk()
            ->assertSee('Unit Kerja')
            ->assertSee('Tambah Unit Kerja');

        $payload = [
            'department' => 'Production',
            'unit_kerja' => 'Cement Production',
            'section' => 'Line 2/3 FM Operation',
            'status' => 'active',
        ];

        $this->actingAs($admin)
            ->post(route('admin.organization-sections.store'), $payload)
            ->assertRedirect(route('admin.organization-sections.index'));

        $this->assertDatabaseHas('organization_sections', $payload);

        $section = OrganizationSection::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.organization-sections.update', $section), [
                'department' => 'Production',
                'unit_kerja' => 'Cement Production',
                'section' => 'Plan Eval & Product Dist',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('admin.organization-sections.index'));

        $this->assertDatabaseHas('organization_sections', [
            'id' => $section->id,
            'section' => 'Plan Eval & Product Dist',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.organization-sections.destroy', $section))
            ->assertRedirect(route('admin.organization-sections.index'));

        $this->assertDatabaseMissing('organization_sections', ['id' => $section->id]);
    }
}
