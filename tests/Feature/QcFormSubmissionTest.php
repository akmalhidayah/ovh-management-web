<?php

namespace Tests\Feature;

use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QcFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_draft_qc_submission(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->payload($template, $block, $row, 'draft'))
            ->assertRedirect(route('user.qc.drafts.index'))
            ->assertSessionHas('success', 'Draft QC berhasil disimpan.');

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('draft', $submission->status);
        $this->assertSame('Crusher Rotor', $submission->equipment);
        $this->assertSame(1, $submission->rows()->count());

        $this->actingAs($user)
            ->get(route('user.qc.drafts.index'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Template Aktif QC');
    }

    public function test_user_can_submit_qc_submission_and_open_pdf(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->payload($template, $block, $row, 'submit'))
            ->assertRedirect(route('user.qc.history.index'))
            ->assertSessionHas('success', 'Form QC berhasil disubmit.');

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('submitted', $submission->status);
        $this->assertNotNull($submission->submitted_at);

        $this->actingAs($user)
            ->get(route('user.qc.history.index'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Menunggu Review');

        $this->actingAs($user)
            ->get(route('user.qc.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Tidak Retak');

        $this->actingAs($user)
            ->get(route('user.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_admin_can_access_submission_pdf(): void
    {
        [$user, $template, $block, $row] = $this->makeActiveTemplate();
        $admin = User::factory()->create(['usertype' => 'admin', 'role' => 'admin']);

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->payload($template, $block, $row, 'submit'))
            ->assertRedirect();

        $submission = QcFormSubmission::firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.qc.submissions.index'))
            ->assertRedirect(route('admin.qc'));

        $this->actingAs($admin)
            ->get(route('admin.qc'))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('PDF')
            ->assertDontSee('Detail');

        $this->actingAs($admin)
            ->get("/admin/qc/submissions/{$submission->id}")
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    private function makeActiveTemplate(): array
    {
        $user = User::factory()->create([
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $template = QcFormTemplate::create([
            'code' => 'QCR-ACTIVE-SUBMIT-001',
            'name' => 'Template Aktif QC',
            'category' => 'Crusher',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
        ]);

        $block = $template->blocks()->create([
            'type' => 'checklist_table',
            'title' => 'Item Pengecekan',
            'order_no' => 1,
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

        $row = $block->tableRows()->create([
            'qc_form_template_id' => $template->id,
            'order_no' => 1,
            'row_data' => [
                'kategori' => 'Crusher',
                'item' => 'Hammer',
                'standar' => 'Tidak Retak',
            ],
        ]);

        return [$user, $template, $block, $row];
    }

    private function payload(QcFormTemplate $template, $block, $row, string $action): array
    {
        return [
            'template_id' => $template->id,
            'action' => $action,
            'general_info' => [
                'report_no' => '',
                'ovh_plant' => 'Tonasa 4',
                'tahun' => '2026',
                'unit' => 'Crusher Area',
                'alat' => 'Crusher Rotor',
                'tag_num' => 'CR-01',
                'tgl_mulai' => '2026-05-06',
                'pekerjaan' => 'Penggantian Hammer',
                'durasi' => '8 Jam',
            ],
            'rows' => [
                $block->id => [
                    $row->id => [
                        'kategori' => 'Crusher',
                        'item' => 'Hammer',
                        'standar' => 'Tidak Retak',
                        'status_value' => 'OK',
                        'catatan' => 'Normal',
                    ],
                ],
            ],
            'note' => 'Catatan umum QC',
            'approval' => [
                'tanggal' => '2026-05-06',
                'diisi' => [
                    'signature' => 'data:image/png;base64,'.base64_encode('signature'),
                    'name' => 'User QC',
                    'role' => 'Quality Control Personil',
                    'signed_at' => now()->toISOString(),
                ],
            ],
        ];
    }
}
