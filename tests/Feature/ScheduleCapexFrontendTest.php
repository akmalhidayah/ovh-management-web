<?php

namespace Tests\Feature;

use App\Support\ScheduleCapexPageData;
use Tests\TestCase;

class ScheduleCapexFrontendTest extends TestCase
{
    public function test_capex_schedule_renders_four_tabs_dashboard_and_modals(): void
    {
        $this->view('modules.schedule-content', [
            'workType' => 'capex',
            'workTypeLabel' => 'Rencana Kerja Capex',
        ])->assertSeeInOrder([
            'Dashboard',
            'Disetujui',
            'Dibatalkan',
            'Rekap Ajuan',
            'ANGGARAN',
            '106,5',
            'ITEM AJUAN',
            '157',
            'REALISASI BIAYA',
            'AJUAN PLANT',
            'RENCANA KERJA CAPEX',
            'REMARKS',
            'DAFTAR AJUAN CAPEX',
            'SHORT TEXT MATERIAL',
            'CANCEL',
            'RINCIAN INFORMASI &amp; STATUS',
            'Overhaul Remark &amp; Note App',
        ], false);
    }

    public function test_capex_module_is_independent_from_other_schedule_modules(): void
    {
        $html = $this->view('modules.schedule-capex.tabs')->__toString();

        $this->assertStringContainsString('data-sc', $html);
        $this->assertStringNotContainsString('data-sb', $html);
        $this->assertStringNotContainsString('data-sj', $html);
        $this->assertStringNotContainsString('schedule-barang', $html);
        $this->assertStringNotContainsString('schedule-jasa', $html);
    }

    public function test_dummy_rows_keep_cancelled_items_without_bom_approval(): void
    {
        $rows = ScheduleCapexPageData::make()['rows'];
        $statuses = [
            ScheduleCapexPageData::STATUS_APPROVED,
            ScheduleCapexPageData::STATUS_PENDING,
            ScheduleCapexPageData::STATUS_CANCELLED,
        ];

        $this->assertNotEmpty($rows);
        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'id'))));
        foreach ($rows as $row) {
            $this->assertContains($row['status'], $statuses);
            if ($row['status'] === ScheduleCapexPageData::STATUS_CANCELLED) {
                $this->assertSame('NO', $row['bom_status']);
            }
        }
    }
}
