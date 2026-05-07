<?php

namespace Tests\Feature;

use App\Models\QcFormTemplate;
use App\Models\User;
use App\Support\QcTemplates\QcTemplateRegistry;
use Database\Seeders\QcFormTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTemplateFormQcTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_template_form_qc_pages_render(): void
    {
        $admin = User::factory()->create([
            'usertype' => 'admin',
            'role' => 'admin',
        ]);

        $template = QcFormTemplate::create([
            'code' => 'QCR-TEST-001',
            'name' => 'Template QC Test',
            'category' => 'QC',
            'description' => 'Template render test',
            'version' => '1.0',
            'status' => 'draft',
            'layout_mode' => 'block_based',
            'created_by' => $admin->id,
        ]);

        $block = $template->blocks()->create([
            'type' => 'checklist_table',
            'title' => 'Checklist QC',
            'order_no' => 1,
            'config' => ['columns' => ['No', 'Aktivitas', 'Standar', 'Aktual', 'Keterangan']],
        ]);

        $block->tableRows()->create([
            'qc_form_template_id' => $template->id,
            'order_no' => 1,
            'row_data' => ['activity' => 'Cek visual', 'standard' => 'OK', 'actual_type' => 'text'],
        ]);

        $this->actingAs($admin);

        foreach ([
            route('admin.template-form-qc.index'),
            route('admin.template-form-qc.create'),
            route('admin.template-form-qc.import'),
            route('admin.template-form-qc.show', $template),
            route('admin.template-form-qc.edit', $template),
            route('admin.template-form-qc.preview', $template),
            route('admin.template-form-commissioning.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_duplicate_and_toggle_template_form_qc(): void
    {
        $admin = User::factory()->create([
            'usertype' => 'admin',
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.template-form-qc.store'), [
            'code' => 'QCR-MANUAL-001',
            'name' => 'Manual Template QC',
            'category' => 'QC',
            'description' => 'Manual template',
            'version' => '1.0',
            'status' => 'draft',
            'blocks' => [
                [
                    'type' => 'checklist_table',
                    'title' => 'Checklist QC',
                    'columns' => 'No, Aktivitas, Standar, Aktual, Keterangan',
                    'rows' => [
                        ['activity' => 'Cek belt', 'standard' => 'Tidak sobek', 'actual_type' => 'text', 'note' => ''],
                    ],
                ],
            ],
        ]);

        $template = QcFormTemplate::where('code', 'QCR-MANUAL-001')->firstOrFail();

        $response->assertRedirect(route('admin.template-form-qc.preview', $template));
        $this->assertSame(1, $template->blocks()->count());
        $this->assertSame(1, $template->tableRows()->count());

        $this->post(route('admin.template-form-qc.duplicate', $template))
            ->assertRedirect();

        $this->assertDatabaseHas('qc_form_templates', [
            'name' => 'Copy - Manual Template QC',
            'status' => 'draft',
        ]);

        $this->patch(route('admin.template-form-qc.toggle-status', $template))
            ->assertRedirect();

        $this->assertDatabaseHas('qc_form_templates', [
            'id' => $template->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_publish_template_form_qc(): void
    {
        $admin = User::factory()->create([
            'usertype' => 'admin',
            'role' => 'admin',
        ]);

        $template = QcFormTemplate::create([
            'code' => 'QCR-PUBLISH-001',
            'name' => 'Template Publish QC',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'draft',
            'layout_mode' => 'block_based',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.template-form-qc.publish', $template))
            ->assertRedirect()
            ->assertSessionHas('success', 'Template berhasil dipublish dan sudah bisa digunakan user QC.');

        $this->assertDatabaseHas('qc_form_templates', [
            'id' => $template->id,
            'status' => 'active',
        ]);
    }

    public function test_user_qc_create_form_renders_active_template_only(): void
    {
        $user = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $activeTemplate = QcFormTemplate::create([
            'code' => 'QCR-ACTIVE-001',
            'name' => 'Template Aktif QC',
            'category' => 'Crusher',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
        ]);

        $draftTemplate = QcFormTemplate::create([
            'code' => 'QCR-DRAFT-001',
            'name' => 'Template Draft QC',
            'category' => 'Crusher',
            'version' => '1.0',
            'status' => 'draft',
            'layout_mode' => 'block_based',
        ]);

        $generalInfo = $activeTemplate->blocks()->create([
            'type' => 'general_info',
            'title' => 'Informasi Umum',
            'order_no' => 1,
            'config' => ['columns' => []],
        ]);

        $generalInfo->fields()->create([
            'qc_form_template_id' => $activeTemplate->id,
            'field_name' => 'alat',
            'label' => 'Alat',
            'type' => 'text',
            'options' => ['default' => 'Crusher Rotor'],
            'order_no' => 1,
        ]);

        $checklist = $activeTemplate->blocks()->create([
            'type' => 'checklist_table',
            'title' => 'Item Pengecekan',
            'order_no' => 2,
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

        $checklist->tableRows()->create([
            'qc_form_template_id' => $activeTemplate->id,
            'order_no' => 1,
            'row_data' => [
                'kategori' => 'Crusher',
                'item' => 'Hammer',
                'standar' => 'Tidak Retak',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', ['template' => $activeTemplate->id]))
            ->assertOk()
            ->assertSee('Template Aktif QC')
            ->assertSee('Crusher Rotor')
            ->assertSee('Hammer')
            ->assertSee('Tidak Retak')
            ->assertDontSee('Template Draft QC');

        $this->assertSame('draft', $draftTemplate->fresh()->status);
    }

    public function test_user_qc_create_form_is_safe_without_active_template(): void
    {
        $user = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        QcFormTemplate::create([
            'code' => 'QCR-DRAFT-ONLY-001',
            'name' => 'Template Draft Saja',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'draft',
            'layout_mode' => 'block_based',
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.forms.create'))
            ->assertOk()
            ->assertSee('Belum ada template QC yang aktif.')
            ->assertDontSee('Template Draft Saja');
    }

    public function test_excel_grid_preview_renders_for_admin(): void
    {
        $admin = User::factory()->create([
            'usertype' => 'admin',
            'role' => 'admin',
        ]);

        $template = QcFormTemplate::create([
            'code' => 'QCR-GRID-001',
            'name' => 'Quality Control Record Grid',
            'category' => 'Crusher',
            'version' => '1.0',
            'status' => 'draft',
            'layout_mode' => 'excel_grid',
            'created_by' => $admin->id,
        ]);

        $template->gridCells()->createMany([
            [
                'row_start' => 1,
                'col_start' => 1,
                'row_span' => 2,
                'col_span' => 2,
                'cell_type' => 'logo',
                'value_default' => 'assets/images/logo/logo-sig.png',
                'order_no' => 1,
            ],
            [
                'row_start' => 1,
                'col_start' => 3,
                'row_span' => 2,
                'col_span' => 4,
                'cell_type' => 'static',
                'label' => 'Quality Control Record',
                'css_class' => 'qc-excel-title',
                'order_no' => 2,
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.template-form-qc.preview', $template))
            ->assertOk()
            ->assertSee('Quality Control Record')
            ->assertSee('qc-excel-sheet', false);
    }

    public function test_qc_template_presets_are_seeded_idempotently(): void
    {
        $expectedCodes = QcTemplateRegistry::codes();

        $this->seed(QcFormTemplateSeeder::class);
        $this->seed(QcFormTemplateSeeder::class);

        $this->assertSame(31, QcFormTemplate::whereIn('code', $expectedCodes)->count());
        $this->assertSame(31, QcFormTemplate::count());
        $this->assertSame(
            ['01', '02', '03', '04', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34'],
            QcTemplateRegistry::numbers()
        );
        $this->assertDatabaseMissing('qc_form_templates', ['code' => 'QCR-05']);
        $this->assertDatabaseMissing('qc_form_templates', ['code' => 'QCR-06']);
        $this->assertDatabaseMissing('qc_form_templates', ['code' => 'QCR-07']);

        $template = QcFormTemplate::where('code', 'QCR-CR-HSGB-001')->firstOrFail();
        $this->assertSame('block_based', $template->layout_mode);
        $this->assertSame(5, $template->blocks()->count());
        $this->assertSame(23, $template->tableRows()->count());
        $this->assertDatabaseHas('qc_form_template_blocks', [
            'qc_form_template_id' => $template->id,
            'type' => 'attachment',
            'title' => 'Lampiran Foto',
        ]);

        $approvalBlock = $template->blocks()->where('type', 'approval')->firstOrFail();
        $this->assertSame([
            ['key' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
            ['key' => 'diisi', 'label' => '*1 Diisi', 'type' => 'signature'],
            ['key' => 'disetujui_1', 'label' => '*2 Disetujui', 'type' => 'signature_locked'],
            ['key' => 'disetujui_2', 'label' => '*3 Disetujui', 'type' => 'signature_locked'],
        ], $approvalBlock->config['columns']);
    }
}
