<?php

namespace Tests\Feature;

use App\Support\ScheduleJasaPageData;
use Tests\TestCase;

class ScheduleJasaFrontendTest extends TestCase
{
    public function test_jasa_schedule_renders_five_tabs_dashboard_and_modals(): void
    {
        $this->view('modules.schedule-content', [
            'workType' => 'jasa',
            'workTypeLabel' => 'Rencana Kerja Jasa',
        ])->assertSeeInOrder([
            'Monitoring Rencana Kerja Jasa',
            'Dashboard',
            'Disetujui',
            'Dikerjakan Intern',
            'Dibatalkan',
            'Rekap Ajuan',
            'ANGGARAN',
            '198,7',
            'ITEM AJUAN',
            'REALISASI BIAYA',
            'AJUAN PLANT',
            'RENCANA KERJA JASA',
            'REMARKS',
            'DAFTAR AJUAN JASA',
            'PROSES PENGADAAN',
            'RINCIAN INFORMASI &amp; STATUS',
            'Reset Form',
            'Overhaul Remark &amp; Note App',
        ], false);
    }

    public function test_jasa_module_is_independent_from_barang_assets(): void
    {
        $html = $this->view('modules.schedule-jasa.tabs')->__toString();

        $this->assertStringContainsString('data-sj', $html);
        $this->assertStringNotContainsString('data-sb', $html);
        $this->assertStringNotContainsString('schedule-barang', $html);
    }

    public function test_dummy_rows_only_use_known_statuses_and_unique_ids(): void
    {
        $rows = ScheduleJasaPageData::make()['rows'];
        $statuses = array_merge(
            [ScheduleJasaPageData::STATUS_APPROVED, ScheduleJasaPageData::STATUS_CANCELLED],
            ScheduleJasaPageData::STATUS_INTERNAL,
        );

        $this->assertNotEmpty($rows);
        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'id'))));
        foreach ($rows as $row) {
            $this->assertContains($row['status'], $statuses);
        }
    }
}
