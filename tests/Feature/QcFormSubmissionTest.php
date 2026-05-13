<?php

namespace Tests\Feature;

use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use App\Models\MasterDataRecord;
use App\Models\User;
use App\Support\QcTemplates\FixedQcTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_user_can_store_fixed_qc_draft_without_complete_data(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), [
                'template_id' => $template->id,
                'action' => 'draft',
            ])
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('draft', $submission->status);
        $this->assertSame([], $submission->body_data['general_rows']);
    }

    public function test_fixed_qc_form_shows_auto_doc_number_and_reordered_header(): void
    {
        Carbon::setTestNow('2026-05-08 10:00:00');

        try {
            [$user, $template] = $this->makeFixedGeneralTemplate();

            $this->actingAs($user)
                ->get(route('user.qc.forms.create', ['template' => $template->id]))
                ->assertOk()
                ->assertSee('001/QC/05-2026')
                ->assertSeeInOrder(['Doc.Number', 'Plant', 'Section', 'Functional Location', 'ID Equipment', 'Name Equipment'], false);

            $this->actingAs($user)
                ->post(route('user.qc.forms.store'), [
                    'template_id' => $template->id,
                    'action' => 'draft',
                ])
                ->assertRedirect(route('user.qc.drafts.index'));

            $submission = QcFormSubmission::firstOrFail();
            $this->assertSame('001/QC/05-2026', $submission->form_number);
            $this->assertSame('001/QC/05-2026', $submission->report_no);
            $this->assertSame('001/QC/05-2026', $submission->general_info['doc_number']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_fixed_qc_name_equipment_uses_active_qc_master_data(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $activeRecord = MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-ACTIVE-QC',
            'equipment_no' => 'EQ-ACTIVE-001',
            'section_no' => 'SEC-ACTIVE-001',
            'description' => 'ACTIVE BELT CONVEYOR',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_QC,
            'year' => '2026',
            'func_location' => 'ST-INACTIVE-QC',
            'equipment_no' => 'EQ-INACTIVE-001',
            'section_no' => 'SEC-INACTIVE-001',
            'description' => 'INACTIVE EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'inactive',
        ]);

        MasterDataRecord::create([
            'document_category' => MasterDataRecord::CATEGORY_COMMISSIONING,
            'year' => '2026',
            'func_location' => 'ST-COMMISSIONING',
            'equipment_no' => 'EQ-COM-001',
            'section_no' => 'SEC-COM-001',
            'description' => 'COMMISSIONING EQUIPMENT',
            'plant' => 'TONASA 4',
            'area' => 'RAW MILL',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertOk()
            ->assertSee('Pilih Name Equipment')
            ->assertSee('ACTIVE BELT CONVEYOR')
            ->assertSee('EQ-ACTIVE-001')
            ->assertDontSee('ST-INACTIVE-QC')
            ->assertDontSee('ST-COMMISSIONING');

        $payload = $this->fixedGeneralPayload($template);
        $payload['header']['master_data_record_id'] = $activeRecord->id;
        $payload['header']['plant'] = 'PLANT-WRONG';
        $payload['header']['functional_location'] = 'MANUAL-WRONG';
        $payload['header']['tag_num'] = 'TAG-WRONG';
        $payload['header']['area'] = 'AREA-WRONG';
        $payload['header']['id_equipment'] = 'EQ-WRONG';
        $payload['header']['name_equipment'] = 'EQP-WRONG';
        $payload['header']['inspector_qc'] = 'INSPECTOR-WRONG';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertSame($activeRecord->id, $submission->general_info['master_data_record_id']);
        $this->assertSame('TONASA 4', $submission->general_info['plant']);
        $this->assertSame('ST-ACTIVE-QC', $submission->general_info['functional_location']);
        $this->assertSame('2026', $submission->general_info['tahun']);
        $this->assertSame('SEC-ACTIVE-001', $submission->general_info['tag_num']);
        $this->assertSame('RAW MILL', $submission->general_info['area']);
        $this->assertSame('EQ-ACTIVE-001', $submission->general_info['id_equipment']);
        $this->assertSame('ACTIVE BELT CONVEYOR', $submission->general_info['name_equipment']);
        $this->assertSame($user->name, $submission->general_info['inspector_qc']);
        $this->assertSame('TONASA 4', $submission->plant);
        $this->assertSame('SEC-ACTIVE-001', $submission->tag_num);
        $this->assertSame('RAW MILL', $submission->area);
    }

    public function test_user_can_continue_fixed_qc_draft_with_saved_data(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        $payload['action'] = 'draft';
        $payload['header']['doc_number'] = 'DOC-DRAFT-001';
        $payload['body']['general_rows'][0]['actual'] = 'Normal Draft';

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.drafts.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertNotSame('DOC-DRAFT-001', $submission->form_number);

        $this->actingAs($user)
            ->get(route('user.qc.submissions.edit', $submission))
            ->assertOk()
            ->assertSee($submission->form_number)
            ->assertSee('Normal Draft')
            ->assertSee('Catatan fixed general');

        $payload['body']['general_rows'][0]['actual'] = 'Normal Updated';
        $payload['note'] = 'Catatan draft diperbarui';

        $this->actingAs($user)
            ->patch(route('user.qc.submissions.update', $submission), $payload)
            ->assertRedirect(route('user.qc.drafts.index'))
            ->assertSessionHas('success', 'Draft QC berhasil diperbarui.');

        $submission->refresh();

        $this->assertSame(1, QcFormSubmission::count());
        $this->assertSame('draft', $submission->status);
        $this->assertSame('Normal Updated', $submission->body_data['general_rows'][0]['actual']);
        $this->assertSame('Catatan draft diperbarui', $submission->note);
        $this->assertSame(1, $submission->rows()->count());
    }

    public function test_user_cannot_submit_fixed_qc_without_final_check(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();
        $payload = $this->fixedGeneralPayload($template);
        unset($payload['body']['final_check']);

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors(['body.final_check' => 'Submit QC hanya bisa dilakukan jika Final Check sudah dicentang.']);

        $this->assertSame(0, QcFormSubmission::count());
    }

    public function test_user_can_submit_fixed_general_qc_and_open_pdf(): void
    {
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedGeneralPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('submitted', $submission->status);
        $this->assertSame('Template Fixed General', $submission->template->name);
        $this->assertTrue($submission->body_data['final_check']);
        $this->assertSame($user->name, $submission->general_info['inspector_qc']);

        $pdfUrl = route('user.qc.submissions.pdf', $submission);
        $this->assertStringContainsString(QcFormSubmission::routeKeyFromFormNumber($submission->form_number), $pdfUrl);
        $this->assertStringNotContainsString("/submissions/{$submission->id}/pdf", $pdfUrl);

        $this->actingAs($user)
            ->get($pdfUrl)
            ->assertOk();

        $this->actingAs($user)
            ->get("/user/qc/submissions/{$submission->id}/pdf")
            ->assertNotFound();
    }

    public function test_qc_attachment_must_be_jpg_or_png_and_is_served_through_authorized_route(): void
    {
        Storage::fake('local');
        [$user, $template] = $this->makeFixedGeneralTemplate();

        $invalidPayload = $this->fixedGeneralPayload($template);
        $invalidPayload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->create('payload.svg', 1, 'image/svg+xml')],
        ];

        $this->actingAs($user)
            ->from(route('user.qc.forms.create', ['template' => $template->id]))
            ->post(route('user.qc.forms.store'), $invalidPayload)
            ->assertRedirect(route('user.qc.forms.create', ['template' => $template->id]))
            ->assertSessionHasErrors('attachments.foto_before.0');

        $validPayload = $this->fixedGeneralPayload($template);
        $validPayload['attachments'] = [
            'foto_before' => [UploadedFile::fake()->image('before.jpg')],
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $validPayload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $attachment = $submission->attachments()->firstOrFail();

        Storage::disk('local')->assertExists($attachment->file_path);
        $this->assertFalse(Storage::disk('public')->exists($attachment->file_path));

        $this->actingAs($user)
            ->get(route('user.qc.attachments.show', $attachment))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_user_can_submit_fixed_welding_qc_and_open_pdf(): void
    {
        [$user, $template] = $this->makeFixedWeldingTemplate();

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $this->fixedWeldingPayload($template))
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();
        $this->assertSame('submitted', $submission->status);
        $this->assertContains('Final Check', $submission->body_data['check_steps']);
        $this->assertSame($user->name, $submission->general_info['inspector_qc']);

        $this->actingAs($user)
            ->get(route('user.qc.submissions.pdf', $submission))
            ->assertOk();
    }

    public function test_fixed_welding_qc_uses_only_admin_template_rows(): void
    {
        [$user, $template] = $this->makeFixedWeldingTemplate();
        $payload = $this->fixedWeldingPayload($template);
        $payload['body']['welder_rows'][] = [
            'no' => '99',
            'nama_welder' => 'Injected Welder',
            'posisi_pengelasan' => '9G',
            'diameter_electrode' => '9.9',
            'electrode_filter' => 'Injected',
            'amper' => '999',
            'keterangan' => 'Injected',
        ];
        $payload['body']['result_rows'][] = [
            'no' => '99',
            'deskripsi' => 'Injected Result',
            'status' => 'Baik',
            'keterangan' => 'Injected',
        ];

        $this->actingAs($user)
            ->post(route('user.qc.forms.store'), $payload)
            ->assertRedirect(route('user.qc.history.index'));

        $submission = QcFormSubmission::firstOrFail();

        $this->assertCount(1, $submission->body_data['welder_rows']);
        $this->assertCount(1, $submission->body_data['result_rows']);
        $this->assertSame('Visual welding', $submission->body_data['result_rows'][0]['deskripsi']);
        $this->assertSame(1, $submission->rows()->where('block_type', 'welding_welder')->count());
        $this->assertSame(1, $submission->rows()->where('block_type', 'welding_result')->count());
        $this->assertDatabaseMissing('qc_form_submission_rows', [
            'block_type' => 'welding_welder',
            'catatan' => 'Injected',
        ]);
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

    private function makeFixedGeneralTemplate(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $template = QcFormTemplate::create([
            'code' => 'QCR-FIXED-GENERAL-SUBMIT-001',
            'name' => 'Template Fixed General',
            'category' => 'QC',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_GENERAL,
            'body_schema' => [
                'rows' => [
                    ['item_pengecekan' => 'Cek bearing', 'standar' => 'Normal', 'actual_default' => '', 'urutan' => 1],
                ],
            ],
        ]);

        return [$user, $template];
    }

    private function makeFixedWeldingTemplate(): array
    {
        $user = User::factory()->create(['usertype' => 'user', 'role' => 'qc']);

        $template = QcFormTemplate::create([
            'code' => 'QCR-FIXED-WELDING-SUBMIT-001',
            'name' => 'Template Fixed Welding',
            'category' => 'Welding',
            'version' => '1.0',
            'status' => 'active',
            'layout_mode' => 'block_based',
            'template_type' => FixedQcTemplate::TYPE_WELDING,
            'body_schema' => [
                'welder_rows' => [
                    ['no' => 1, 'nama_welder' => 'Welder A', 'posisi_pengelasan' => '1G', 'diameter_electrode' => '3.2', 'electrode_filter' => 'E7018', 'amper' => '90', 'keterangan' => ''],
                ],
                'result_rows' => [
                    ['no' => 1, 'deskripsi' => 'Visual welding', 'keterangan' => ''],
                ],
            ],
        ]);

        return [$user, $template];
    }

    private function fixedHeader(): array
    {
        return [
            'doc_number' => 'DOC-QC-001',
            'plant' => 'TONASA 4',
            'tag_num' => 'TAG-01',
            'functional_location' => 'FL-001',
            'id_equipment' => 'EQ-001',
            'name_equipment' => 'Bearing Conveyor',
            'area' => 'Crusher Area',
            'date_time' => '2026-05-08T10:00',
            'inspector_qc' => 'Manual Inspector',
            'pekerjaan' => 'Inspection',
            'unit_kerja' => 'Maintenance',
            'durasi' => '240',
        ];
    }

    private function fixedGeneralPayload(QcFormTemplate $template): array
    {
        return [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => $this->fixedHeader(),
            'body' => [
                'final_check' => '1',
                'general_rows' => [
                    [
                        'item_pengecekan' => 'Cek bearing',
                        'standar' => 'Normal',
                        'actual' => 'Normal',
                        'status' => 'Ok',
                        'catatan' => 'Aman',
                    ],
                ],
            ],
            'note' => 'Catatan fixed general',
            'approval' => [],
        ];
    }

    private function fixedWeldingPayload(QcFormTemplate $template): array
    {
        return [
            'template_id' => $template->id,
            'action' => 'submit',
            'header' => $this->fixedHeader(),
            'body' => [
                'methods' => ['Visual Check'],
                'check_steps' => ['1', 'Final Check'],
                'final_check' => '1',
                'welder_rows' => [
                    [
                        'no' => '1',
                        'nama_welder' => 'Welder A',
                        'posisi_pengelasan' => '1G',
                        'diameter_electrode' => '3.2',
                        'electrode_filter' => 'E7018',
                        'amper' => '90',
                        'keterangan' => 'OK',
                    ],
                ],
                'result_rows' => [
                    [
                        'no' => '1',
                        'deskripsi' => 'Visual welding',
                        'status' => 'Baik',
                        'keterangan' => 'Rapi',
                    ],
                ],
            ],
            'note' => 'Catatan fixed welding',
            'approval' => [],
        ];
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
