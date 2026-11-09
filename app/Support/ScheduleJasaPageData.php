<?php

namespace App\Support;

/**
 * Data dummy untuk tampilan Rencana Kerja Jasa (frontend saja).
 * Modul ini sengaja terpisah dari Rencana Kerja Barang (ScheduleBarangPageData) agar mudah dirawat.
 * Ganti isi make() dengan data dari database saat backend siap; bentuk array dipakai langsung oleh
 * view modules.schedule-jasa.* dan public/assets/js/schedule-jasa.js.
 */
class ScheduleJasaPageData
{
    public const STATUS_APPROVED = 'Lanjut';

    public const STATUS_CANCELLED = 'Batal';

    /** Status "Dikerjakan Intern": dikerjakan sendiri oleh PGO, TS, atau BMS. */
    public const STATUS_INTERNAL = ['PGO', 'TS', 'BMS'];

    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        return [
            'updatedAt' => '12/11/2026, 15:40',
            'years' => [2026, 2027, 2025],
            'plants' => ['Tonasa 2', 'Tonasa 3', 'Tonasa 4', 'Tonasa 5', 'Power Plant'],
            'budget' => [
                ['label' => 'Rencana Anggaran', 'value' => '180', 'unit' => 'Miliar', 'icon' => 'bi-calendar2-check'],
                ['label' => 'Total Ajuan', 'value' => '198,7', 'unit' => 'Miliar', 'icon' => 'bi-card-checklist'],
                ['label' => 'Total Disetujui', 'value' => '162,1', 'unit' => 'Miliar', 'icon' => 'bi-check-circle', 'round' => true],
            ],
            'items' => [
                ['label' => 'Total Ajuan', 'value' => '170', 'icon' => 'bi-calendar2-check'],
                ['label' => 'Disetujui', 'value' => '130', 'icon' => 'bi-check-circle', 'round' => true],
                ['label' => 'Dikerjakan PGO & TS', 'value' => '20', 'icon' => 'bi-person-badge'],
                ['label' => 'Dibatalkan', 'value' => '10', 'icon' => 'bi-x-circle', 'round' => true],
            ],
            'costPercent' => 56,
            'plantTotals' => [
                ['plant' => 'Tonasa 2', 'value' => 'Rp 0,9', 'change' => '2%'],
                ['plant' => 'Tonasa 3', 'value' => 'Rp 0,9', 'change' => '2%'],
                ['plant' => 'Tonasa 4', 'value' => 'Rp 0,9', 'change' => '2%'],
                ['plant' => 'Tonasa 5', 'value' => 'Rp 0,9', 'change' => '2%'],
                ['plant' => 'Power Plant', 'value' => 'Rp 0,9', 'change' => '2%'],
            ],
            'plantTotalAll' => 'Rp 0,9',
            'unitRecap' => self::unitRecap(),
            'remarks' => self::remarks(),
            'rows' => self::rows(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function unitRecap(): array
    {
        // [unit, plant, pagu (Rp), ajuan (Rp)]
        $base = [
            ['SEKSI PEM. MESIN KCM 4/5', 'Tonasa 4', 2700000000, 2354923037],
            ['SEKSI OPERASI RKC 4/5', 'Tonasa 4', 50000000, 31057500],
            ['SEKSI PEM. MESIN FM 4/5', 'Tonasa 4', 2200000000, 51684100],
            ['SEKSI PEM. MESIN RM 4/5', 'Tonasa 4', 2350000000, 2845986000],
            ['SEKSI OPERASI FM 4/5', 'Tonasa 4', 50000000, 168707171],
            ['SEKSI PEM. LISTRIK PABRIK 4', 'Tonasa 4', 900000000, 640000000],
            ['SEKSI PEM. INSTRUMEN 5', 'Tonasa 5', 650000000, 701500000],
            ['SEKSI OPERASI KILN 5', 'Tonasa 5', 120000000, 98200000],
            ['SEKSI PEM. MESIN CRUSHER 5', 'Tonasa 5', 1450000000, 1390000000],
            ['SEKSI OPERASI COAL MILL 5', 'Tonasa 5', 75000000, 81300000],
            ['SEKSI PEM. MESIN FM 2/3', 'Tonasa 2', 1800000000, 1690000000],
            ['SEKSI PEM. ELINS FM 2/3', 'Tonasa 2', 1300000000, 1240500000],
            ['SEKSI PEMEL. EP/DC', 'Tonasa 2', 500000000, 441000000],
            ['SEKSI BENGKEL MESIN', 'Tonasa 3', 300000000, 243000000],
            ['SEKSI OPERASI FM 3', 'Tonasa 3', 60000000, 74200000],
            ['SEKSI PEM. MESIN RKC 3', 'Tonasa 3', 2100000000, 1980000000],
            ['SEKSI PEM. TURBIN', 'Power Plant', 1900000000, 1750000000],
            ['SEKSI PEM. BOILER', 'Power Plant', 2600000000, 2710000000],
            ['SEKSI OPERASI PLTU', 'Power Plant', 85000000, 62000000],
            ['SEKSI PEM. LISTRIK PLTU', 'Power Plant', 700000000, 695000000],
            ['SEKSI PEM. INSTRUMEN 4', 'Tonasa 4', 540000000, 560800000],
            ['SEKSI OPERASI RAW MILL 5', 'Tonasa 5', 90000000, 41500000],
            ['SEKSI PEM. UTILITAS 5', 'Tonasa 5', 430000000, 388000000],
            ['SEKSI OPERASI PACKING 4', 'Tonasa 4', 70000000, 83900000],
            ['SEKSI PEM. MESIN KILN 5', 'Tonasa 5', 2250000000, 2190000000],
        ];

        return array_map(static function (array $row, int $index): array {
            [$unit, $plant, $pagu, $ajuan] = $row;
            $percent = round($ajuan / $pagu * 100, 1);

            return [
                'no' => $index + 1,
                'unit' => $unit,
                'plant' => $plant,
                'pagu' => self::shortRupiah($pagu),
                'ajuan' => self::shortRupiah($ajuan),
                'percent' => number_format($percent, 1, ',', '.').'%',
                'over' => $percent > 100,
            ];
        }, $base, array_keys($base));
    }

    private static function shortRupiah(int $value): string
    {
        if ($value >= 1000000000) {
            return number_format($value / 1000000000, 2, ',', '.').' M';
        }

        return number_format($value / 1000000, 1, ',', '.').' Jt';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function remarks(): array
    {
        return [
            ['text' => 'Evaluasi spesifikasi Bearing Ball 6205 C3 SKF Tonasa 4, status BOM OK dan disetujui untuk pembelian Q3 2026.', 'detail' => 'Spesifikasi teknis dan jumlah kebutuhan telah sesuai hasil evaluasi Tim Overhaul.', 'status' => 'OK'],
            ['text' => 'Audit pembatalan item material Oil Seal Tonasa 5 (Cancel) seksi operasi RKC 5 karena efisiensi anggaran.', 'detail' => 'Ajuan dibatalkan dan tidak diperhitungkan pada total ajuan lanjut.', 'status' => 'Cancel'],
            ['text' => 'Verifikasi dokumen COA & Technical Drawing Power Plant Seksi Turbin disetujui 100% lengkap.', 'detail' => 'Seluruh dokumen pendukung telah diterima dan tervalidasi.', 'status' => 'OK'],
            ['text' => 'Pembatalan pengadaan Coupling Finish Mill Tonasa 2/3 (Cancel) karena stok existing di gudang masih mencukupi.', 'detail' => 'Kebutuhan akan dievaluasi kembali pada periode overhaul berikutnya.', 'status' => 'Cancel'],
            ['text' => 'Finalisasi pendaftaran garansi pabrikan 12 bulan dan jadwal pengiriman item Conveyor Belt Tonasa 4.', 'detail' => 'Vendor telah memberikan konfirmasi garansi dan estimasi waktu pengiriman.', 'status' => 'OK'],
            ['text' => 'Penyesuaian lingkup jasa rebricking Kiln Tonasa 5 berdasarkan hasil inspeksi aktual.', 'detail' => 'Lingkup akhir telah diselaraskan dengan hasil pengukuran lapangan.', 'status' => 'OK'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function rows(): array
    {
        // [unit kerja, plant, notifikasi, deskripsi pekerjaan, durasi (hari), qty, uom qty, harga satuan]
        $base = [
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942331', 'OH27 - JASA GANTI CHAIN RECLAMER', 7, 1, 'LOT', 65000000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942333', 'OH27 - JASA GANTI LAMELA APRON FEEDER', 5, 1, 'LOT', 70000000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942348', 'OH-27 - GANTI TYER M ROLLER 553, SLEDING MATERIAL', 12, 1, 'LOT', 281288182],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942372', 'OH27 - JASA GANTI SAMBUNGAN BELT BUCKET', 3, 1, 'LOT', 65000000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942569', 'OH27 - JASA GANTI FEEDER SET', 10, 1, 'LOT', 145000000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942570', 'OH27 - JASA BOBOL DINDING FEEDER DAN PASANG DINDING', 5, 1, 'LOT', 45000000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942484', 'OH27 - JASA REPAIR INNERPART MILL', 10, 1, 'LOT', 66000000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942589', 'OH27 - JASA GANTI SAMBUNGAN HOT SPLICING', 3, 1, 'LOT', 21520000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942626', 'OH27 - JASA GANTI CHUTE SLIDE GATE', 4, 1, 'LOT', 67070122],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942628', 'OH27 - JASA SERVICE COOLER', 5, 1, 'LOT', 195040750],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942685', 'OH27 - JASA GANTI HOLIK M ROLLER 03', 4, 1, 'LOT', 109200000],
            ['Seksi Pemel. Mesin FM 4/5', 'Tonasa 5', '300001942699', 'OH27 - JASA GANTI HOLIK M ROLLER 01', 4, 1, 'LOT', 109200000],
            ['Seksi Operasi FM 5', 'Tonasa 5', '300001943939', 'OH27 - JASA LINING HIGH ALUMINA CERAMIC TILES', 5, 10, 'M2', 13000000],
            ['Seksi Operasi FM 5', 'Tonasa 5', '300001943947', 'OH27 - JASA LINING HIGH ALUMINA CERAMIC TILES', 5, 7, 'M2', 13000000],
            ['Seksi Operasi RKC 5', 'Tonasa 5', '300001945651', 'OH27 - JASA REBRICKING KILN TONASA 5 (86 METER)', 18, 1, 'LOT', 895580720],
            ['Seksi Pemel. Listrik RKC 4/5', 'Tonasa 4', '300001946012', 'OH27 - JASA REWINDING MOTOR KILN ID FAN', 14, 1, 'LOT', 410000000],
            ['Seksi Pemel. Turbin', 'Power Plant', '300001946133', 'OH27 - JASA OVERHAUL TURBIN UNIT 2', 21, 1, 'LOT', 1250000000],
        ];

        $statuses = [
            'Lanjut', 'Lanjut', 'BMS', 'TS', 'Batal', 'Lanjut', 'PGO', 'Lanjut', 'Lanjut', 'TS', 'Batal', 'PGO', 'Lanjut', 'Lanjut', 'TS', 'Lanjut', 'Batal',
            'Lanjut', 'Lanjut', 'PGO', 'Lanjut', 'Lanjut', 'BMS', 'Lanjut', 'Batal', 'Lanjut', 'TS', 'Lanjut', 'Lanjut', 'PGO', 'Lanjut', 'Batal', 'Lanjut', 'Lanjut',
        ];
        $processes = ['Kontrak', 'Fabrikasi', 'Tender', 'Tender', 'Cancel', 'Tender', 'Fabrikasi', 'Tender', 'Fabrikasi', 'Tender', 'Cancel', 'Fabrikasi', 'Kontrak', 'BMS Review', 'Kontrak', 'Tender', 'Cancel'];
        $areas = ['Finish Mill 5', 'Raw Mill 5', 'Kiln 5', 'Coal Mill 5'];
        $evaluators = ['Rahmat A.', 'Sari W.', 'Budi S.', 'Amir F.'];

        $rows = [];
        foreach ($statuses as $index => $status) {
            [$unit, $plant, $notification, $description, $duration, $qty, $uom, $price] = $base[$index % count($base)];
            $variant = intdiv($index, count($base));
            if ($variant > 0) {
                $notification = (string) ((int) $notification + 2000 * $variant + $index);
                $price = (int) round($price * 1.04);
            }
            $process = $status === 'Batal' ? 'Cancel' : $processes[$index % count($processes)];

            $rows[] = [
                'id' => 'sj-'.($index + 1),
                'year' => 2026,
                'unit' => $unit,
                'plant' => $plant,
                'notification' => $notification,
                'description' => $description,
                'duration' => $duration,
                'uom_duration' => 'HARI',
                'qty' => $qty,
                'uom_qty' => $uom,
                'price' => $price,
                'status' => $status,
                'process' => $process,
                'area' => $areas[$index % count($areas)],
                'functional_location' => '552-FM'.(($index % 3) + 1).'-EQ0'.(($index % 9) + 1),
                'equipment_id' => 'EQ-552-'.str_pad((string) (194 + $index), 4, '0', STR_PAD_LEFT),
                'detail' => '',
                'evaluation_note' => '',
                // Kolom tambahan untuk tampilan lebar Rekap Ajuan.
                'plant_section' => $plant,
                'qty_bom' => $qty,
                'uom_bom' => $uom === 'M2' ? 'M2' : 'UNIT',
                'mrp_type' => 'PD',
                'lifetime' => '12 BLN',
                'order' => 'ORD-'.(9921 + $index),
                'reservation' => 'RES-'.str_pad((string) (12 + $index), 4, '0', STR_PAD_LEFT),
                'material_no' => (string) (5800001200 + $index * 3),
                'po_ref' => 'PO-2025-'.(881902 + $index),
                'po_date' => '2025-01-12',
                'pic' => $evaluators[$index % count($evaluators)],
                'qty_approved' => $status === 'Batal' ? 0 : $qty,
                'bom_status' => $status === 'Batal' ? 'NO' : 'OK',
                'notes' => $index < 4 ? self::seedNotes($index) : [],
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function seedNotes(int $index): array
    {
        $all = [
            ['id' => 'seed-1', 'date' => '2026-10-20', 'status' => 'Selesai', 'text' => 'Evaluasi lingkup jasa ganti chain reclamer disetujui, jadwal pekerjaan mengikuti window overhaul Q3 2026.'],
            ['id' => 'seed-2', 'date' => '2026-10-21', 'status' => 'In Progress', 'text' => 'Koordinasi vendor jasa lamela apron feeder, menunggu konfirmasi ketersediaan tenaga kerja dan jadwal mobilisasi.'],
            ['id' => 'seed-3', 'date' => '2026-10-22', 'status' => 'Penting', 'text' => 'Verifikasi ulang durasi pekerjaan tyer M roller 553 karena melebihi rencana awal 10 hari.'],
        ];

        return $index === 0 ? $all : [$all[min($index, 2)]];
    }
}
