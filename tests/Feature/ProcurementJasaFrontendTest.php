<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class ProcurementJasaFrontendTest extends TestCase
{
    public function test_procurement_service_dashboard_renders_the_frontend_summary(): void
    {
        $view = $this->view('modules.procurement-content', $this->viewData());

        $view
            ->assertSeeInOrder([
                'Dashboard',
                'Good Issue/Finish',
                'Ready',
                'Procurement',
                'Pending',
                'Cancel',
                'All Item',
            ])
            ->assertSee('Budget Overhaul')
            ->assertSee('Request Budget')
            ->assertSee('Rp 77,3 M')
            ->assertSee('96')
            ->assertSee('Procurement Status')
            ->assertSee('Order Status')
            ->assertDontSee('Dummy data - belum terhubung database');
    }

    public function test_procurement_service_process_tab_renders_compact_frontend_table(): void
    {
        $this->app->instance('request', Request::create('/admin/procurement/jasa', 'GET', [
            'tab' => 'procurement',
        ]));

        $this->view('modules.procurement-content', $this->viewData())
            ->assertSee('Cari Unit Kerja/PIC')
            ->assertSee('Spesifikasi')
            ->assertSee('Export Excel')
            ->assertSee('Download to PDF')
            ->assertSee('Status Pengadaan')
            ->assertSee('Proses dokumen tender')
            ->assertSee('Pagination daftar pengadaan jasa')
            ->assertSee('Edit jasa 1')
            ->assertSee('Lihat detail jasa 1');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        return [
            'section' => 'jasa',
            'plantOptions' => collect(['Tonasa 4', 'Tonasa 5']),
            'yearOptions' => collect(['2026']),
            'currentYear' => '2026',
        ];
    }
}
