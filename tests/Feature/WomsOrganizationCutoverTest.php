<?php

namespace Tests\Feature;

use App\Models\MasterDataRecord;
use App\Models\OrganizationSection;
use App\Models\User;
use App\Models\WomsDepartment;
use App\Models\WomsSection;
use App\Models\WomsUnit;
use App\Services\WomsOrganizationResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WomsOrganizationCutoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_woms_cutover_flags_are_disabled_by_default(): void
    {
        $this->assertFalse((bool) config('services.woms_organization.equipment_enabled'));
        $this->assertFalse((bool) config('services.woms_organization.qc_enabled'));
        $this->assertFalse((bool) config('services.woms_organization.commissioning_enabled'));
        $this->assertFalse((bool) config('services.woms_organization.legacy_read_only'));
    }

    public function test_woms_organization_page_uses_the_new_label_and_hides_the_legacy_menu(): void
    {
        config()->set('services.woms_organization.legacy_read_only', true);
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.woms-organization.index'))
            ->assertOk()
            ->assertSee('Struktur Organisasi')
            ->assertDontSee('Struktur Organisasi WOMS')
            ->assertDontSee('Unit Kerja Lama — Riwayat')
            ->assertDontSee('Salinan lokal read-only')
            ->assertDontSee('departemen ditemukan')
            ->assertDontSee('Ada di WOMS')
            ->assertDontSee('Pejabat Unik')
            ->assertDontSee('Tidak di Sumber')
            ->assertDontSee('Mapping Terverifikasi')
            ->assertDontSee('Status Sumber')
            ->assertDontSee('Hierarki Organisasi')
            ->assertSee('Informasi Sinkronisasi')
            ->assertSee('womsSyncInfoModal')
            ->assertSee('Riwayat Sinkronisasi');
    }

    public function test_resolver_returns_server_owned_present_hierarchy(): void
    {
        $section = $this->createWomsHierarchy();

        $resolved = app(WomsOrganizationResolver::class)->resolvePresent($section->id);

        $this->assertSame($section->id, $resolved['woms_section_id']);
        $this->assertSame(3001, $resolved['woms_section_woms_id']);
        $this->assertSame('Kiln Operation', $resolved['section']);
        $this->assertSame(2001, $resolved['woms_unit_woms_id']);
        $this->assertSame('Clinker Production', $resolved['work_unit']);
        $this->assertSame(1001, $resolved['woms_department_woms_id']);
        $this->assertSame('Production', $resolved['department']);
    }

    public function test_resolver_accepts_canonical_positive_integer_string(): void
    {
        $section = $this->createWomsHierarchy();

        $resolved = app(WomsOrganizationResolver::class)->resolvePresent((string) $section->id);

        $this->assertSame($section->id, $resolved['woms_section_id']);
    }

    public function test_resolver_rejects_fractional_id_without_coercion(): void
    {
        $this->createWomsHierarchy();
        $this->expectException(ValidationException::class);

        app(WomsOrganizationResolver::class)->resolvePresent('1.5');
    }

    public function test_resolver_rejects_missing_section_or_parent(): void
    {
        $section = $this->createWomsHierarchy();
        $section->unit->update(['present_in_source' => false, 'source_missing_at' => now()]);

        $this->expectException(ValidationException::class);

        app(WomsOrganizationResolver::class)->resolvePresent($section->id);
    }

    public function test_resolver_rejects_section_no_longer_present_in_source(): void
    {
        $section = $this->createWomsHierarchy();
        $section->update(['present_in_source' => false, 'source_missing_at' => now()]);

        $this->expectException(ValidationException::class);

        app(WomsOrganizationResolver::class)->resolvePresent($section->id);
    }

    public function test_present_options_keep_duplicate_section_names_distinguishable_by_hierarchy(): void
    {
        $first = $this->createWomsHierarchy();
        $second = $this->createWomsHierarchy(
            departmentWomsId: 1002,
            unitWomsId: 2002,
            sectionWomsId: 3002,
            department: 'Maintenance',
            unit: 'Mechanical Maintenance',
            section: 'Kiln Operation'
        );

        $options = app(WomsOrganizationResolver::class)->presentOptions()->keyBy('woms_section_id');

        $this->assertSame('Production', $options[$first->id]['department']);
        $this->assertSame('Clinker Production', $options[$first->id]['work_unit']);
        $this->assertSame('Maintenance', $options[$second->id]['department']);
        $this->assertSame('Mechanical Maintenance', $options[$second->id]['work_unit']);
        $this->assertSame('Kiln Operation', $options[$first->id]['section']);
        $this->assertSame('Kiln Operation', $options[$second->id]['section']);
    }

    public function test_admin_can_create_commissioning_equipment_with_woms_reference(): void
    {
        config()->set('services.woms_organization.equipment_enabled', true);
        $section = $this->createWomsHierarchy();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.master-data.store'), $this->masterDataPayload([
                'woms_section_id' => $section->id,
            ]))
            ->assertRedirect();

        $record = MasterDataRecord::where('func_location', 'FL-WOMS-001')->firstOrFail();
        $this->assertSame($section->id, $record->woms_section_id);
        $this->assertNull($record->organization_section_id);
    }

    public function test_admin_cannot_create_commissioning_equipment_with_invalid_woms_reference(): void
    {
        config()->set('services.woms_organization.equipment_enabled', true);
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.master-data'))
            ->post(route('admin.master-data.store'), $this->masterDataPayload([
                'woms_section_id' => 999999,
            ]))
            ->assertRedirect(route('admin.master-data'))
            ->assertSessionHasErrors('woms_section_id');

        $this->assertDatabaseMissing('master_data_records', ['func_location' => 'FL-WOMS-001']);
    }

    public function test_equipment_cutover_does_not_require_woms_for_qc_category(): void
    {
        config()->set('services.woms_organization.equipment_enabled', true);
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.master-data.store'), $this->masterDataPayload([
                'document_category' => MasterDataRecord::CATEGORY_QC,
            ]))
            ->assertRedirect();

        $record = MasterDataRecord::where('func_location', 'FL-WOMS-001')->firstOrFail();
        $this->assertSame(MasterDataRecord::CATEGORY_QC, $record->document_category);
        $this->assertNull($record->woms_section_id);
        $this->assertNull($record->organization_section_id);
    }

    public function test_editing_legacy_equipment_preserves_legacy_reference_until_explicit_woms_selection(): void
    {
        config()->set('services.woms_organization.equipment_enabled', true);
        $section = $this->createWomsHierarchy();
        $legacy = OrganizationSection::create([
            'department' => 'Legacy Department',
            'unit_kerja' => 'Legacy Unit',
            'section' => 'Legacy Section',
            'status' => 'active',
        ]);
        $record = MasterDataRecord::create($this->masterDataPayload([
            'organization_section_id' => $legacy->id,
        ]));
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.master-data.update', $record), $this->masterDataPayload([
                'description' => 'UPDATED WITHOUT ORGANIZATION CONVERSION',
            ]))
            ->assertRedirect();

        $record->refresh();
        $this->assertSame($legacy->id, $record->organization_section_id);
        $this->assertNull($record->woms_section_id);

        $this->actingAs($admin)
            ->put(route('admin.master-data.update', $record), $this->masterDataPayload([
                'description' => 'EXPLICIT WOMS CONVERSION',
                'woms_section_id' => $section->id,
            ]))
            ->assertRedirect();

        $record->refresh();
        $this->assertSame($legacy->id, $record->organization_section_id);
        $this->assertSame($section->id, $record->woms_section_id);
    }

    public function test_legacy_master_read_only_mode_keeps_index_and_blocks_mutations(): void
    {
        config()->set('services.woms_organization.legacy_read_only', true);
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);
        $legacy = OrganizationSection::create([
            'department' => 'Legacy Department',
            'unit_kerja' => 'Legacy Unit',
            'section' => 'Legacy Section',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.organization-sections.index'))
            ->assertOk()
            ->assertSee('Unit Kerja Lama — Riwayat')
            ->assertDontSee('Tambah Unit Kerja');

        $this->actingAs($admin)
            ->post(route('admin.organization-sections.store'), [
                'department' => 'Blocked',
                'unit_kerja' => 'Blocked',
                'section' => 'Blocked',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.organization-sections.update', $legacy), [
                'department' => 'Blocked',
                'unit_kerja' => 'Blocked',
                'section' => 'Blocked',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('admin.organization-sections.destroy', $legacy))
            ->assertForbidden();

        $this->assertDatabaseHas('organization_sections', [
            'id' => $legacy->id,
            'section' => 'Legacy Section',
        ]);
    }

    private function createWomsHierarchy(
        int $departmentWomsId = 1001,
        int $unitWomsId = 2001,
        int $sectionWomsId = 3001,
        string $department = 'Production',
        string $unit = 'Clinker Production',
        string $section = 'Kiln Operation'
    ): WomsSection {
        WomsDepartment::create([
            'woms_id' => $departmentWomsId,
            'name' => $department,
            'present_in_source' => true,
            'source_synced_at' => now(),
        ]);
        WomsUnit::create([
            'woms_id' => $unitWomsId,
            'woms_department_id' => $departmentWomsId,
            'name' => $unit,
            'present_in_source' => true,
            'source_synced_at' => now(),
        ]);

        return WomsSection::create([
            'woms_id' => $sectionWomsId,
            'woms_unit_id' => $unitWomsId,
            'name' => $section,
            'present_in_source' => true,
            'source_synced_at' => now(),
        ]);
    }

    private function masterDataPayload(array $overrides = []): array
    {
        return array_replace([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'FL-WOMS-001',
            'equipment_no' => 'EQ-WOMS-001',
            'section_no' => 'SEC-WOMS-001',
            'description' => 'WOMS TEST EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'KILN',
            'status' => 'active',
            'notes' => null,
        ], $overrides);
    }
}
