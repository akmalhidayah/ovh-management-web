<?php

namespace Tests\Feature;

use App\Support\ScheduleBarangPageData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ScheduleBarangFrontendTest extends TestCase
{
    public function test_barang_schedule_renders_four_tabs_dashboard_and_modals(): void
    {
        $this->view('modules.schedule-content', [
            'workType' => 'barang',
            'workTypeLabel' => 'Rencana Kerja Barang',
        ])->assertSeeInOrder([
            'Dashboard',
            'Disetujui',
            'Dibatalkan',
            'Rekap Ajuan',
            'ANGGARAN',
            '230,5',
            'ITEM AJUAN',
            'PERSENTASE ANGGARAN DISETUJUI',
            'AJUAN TIAP PABRIK',
            'REKAP AJUAN UNIT KERJA',
            'CATATAN',
            'DAFTAR AJUAN BARANG',
            'RINCIAN INFORMASI &amp; STATUS',
            'Overhaul Remark &amp; Note App',
        ], false);
    }

    public function test_schedule_overview_route_is_removed(): void
    {
        $this->assertFalse(Route::has('admin.schedule.overview'));
        $this->assertTrue(Route::has('admin.schedule'));
    }

    public function test_dummy_rows_only_use_known_statuses_and_unique_ids(): void
    {
        $rows = ScheduleBarangPageData::make()['rows'];
        $statuses = [
            ScheduleBarangPageData::STATUS_APPROVED,
            ScheduleBarangPageData::STATUS_PENDING,
            ScheduleBarangPageData::STATUS_CANCELLED,
        ];

        $this->assertNotEmpty($rows);
        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'id'))));
        foreach ($rows as $row) {
            $this->assertContains($row['status'], $statuses);
        }
    }
}
