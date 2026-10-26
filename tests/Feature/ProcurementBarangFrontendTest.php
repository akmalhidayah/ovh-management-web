<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class ProcurementBarangFrontendTest extends TestCase
{
    public function test_procurement_goods_dashboard_renders_the_frontend_summary(): void
    {
        $view = $this->view('modules.procurement-content', $this->viewData());

        $view
            ->assertSeeInOrder([
                'Beranda',
                'Pengeluaran Barang',
                'Siap',
                'Pengiriman',
                'Pengadaan',
                'Dokumen &amp; Permintaan Pembelian',
                'Tertunda',
                'Dibatalkan',
                'Tampilkan Semua',
            ], false)
            ->assertSee('Request Budget')
            ->assertSee('Rp 77,3 M')
            ->assertSee('Realisasi')
            ->assertSee('96')
            ->assertSee('Proses Pengadaan')
            ->assertSee('Procurement Progress')
            ->assertSee('Order Status')
            ->assertDontSee('Dummy data - belum terhubung database');
    }

    public function test_procurement_goods_process_tab_uses_the_shared_table(): void
    {
        $this->app->instance('request', Request::create('/admin/procurement/barang', 'GET', [
            'tab' => 'pengadaan',
        ]));

        $this->view('modules.procurement-content', $this->viewData())
            ->assertSee('Unit kerja / spesifikasi / MRP')
            ->assertSee('Status Pengadaan')
            ->assertSee('Proses Penawaran')
            ->assertSee('Pagination daftar pengadaan barang')
            ->assertSee('Edit pengadaan baris 1');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        return [
            'section' => 'barang',
            'plantOptions' => collect(['Tonasa 4', 'Tonasa 5']),
            'yearOptions' => collect(['2026']),
            'currentYear' => '2026',
        ];
    }
}
