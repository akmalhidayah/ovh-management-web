<?php

namespace App\Support;

/**
 * Data dummy untuk tampilan Rencana Kerja Capex (frontend saja).
 * Modul ini sengaja terpisah dari Rencana Kerja Barang dan Jasa agar mudah dirawat.
 * Ganti isi make() dengan data dari database saat backend siap; bentuk array dipakai langsung oleh
 * view modules.schedule-capex.* dan public/assets/js/schedule-capex.js.
 */
class ScheduleCapexPageData
{
    public const STATUS_APPROVED = 'Lanjut';

    public const STATUS_PENDING = 'Pending';

    public const STATUS_CANCELLED = 'Batal';

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
                ['label' => 'Rencana Anggaran', 'value' => '106,5', 'unit' => 'Miliar', 'icon' => null],
                ['label' => 'Total Ajuan', 'value' => '106', 'unit' => 'Miliar', 'icon' => null],
                ['label' => 'Total Disetujui', 'value' => '106', 'unit' => 'Miliar', 'icon' => 'bi-check-lg', 'round' => true],
            ],
            'costPercent' => 96,
            'items' => [
                ['label' => 'Total Ajuan', 'value' => '157', 'icon' => 'bi-check-lg'],
                ['label' => 'Disetujui', 'value' => '151', 'icon' => 'bi-check-lg', 'round' => true],
                ['label' => 'Dibatalkan', 'value' => '6', 'icon' => 'bi-x-lg', 'round' => true],
            ],
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
            ['SEKSI PEM. MESIN KCM 4/5', 'Tonasa 4', 2700000000, 2554923037],
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
            ['text' => 'Audit pembatalan item material Oil Seal Tonasa 5 (Cancel) seksi operasi RKC karena efisiensi anggaran.', 'detail' => 'Ajuan dibatalkan dan tidak diperhitungkan pada total ajuan lanjut.', 'status' => 'Cancel'],
            ['text' => 'Verifikasi dokumen COA & Technical Drawing Power Plant Seksi Turbin disetujui 100% lengkap.', 'detail' => 'Seluruh dokumen pendukung telah diterima dan tervalidasi.', 'status' => 'OK'],
            ['text' => 'Pembatalan pengadaan Coupling Finish Mill Tonasa 2/3 (Cancel) karena stok existing di gudang masih mencukupi.', 'detail' => 'Kebutuhan akan dievaluasi kembali pada periode overhaul berikutnya.', 'status' => 'Cancel'],
            ['text' => 'Finalisasi pendaftaran garansi pabrikan 12 bulan dan jadwal pengiriman item Conveyor Belt Tonasa 4.', 'detail' => 'Vendor telah memberikan konfirmasi garansi dan estimasi waktu pengiriman.', 'status' => 'OK'],
            ['text' => 'Penyesuaian jumlah kebutuhan refractory brick Kiln Tonasa 5 berdasarkan hasil inspeksi aktual.', 'detail' => 'Jumlah akhir telah diselaraskan dengan hasil pengukuran lapangan.', 'status' => 'OK'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function rows(): array
    {
        // [plant, unit kerja, MRP, no. material, short text, qty, uom, harga satuan, BOM, drawing, RIF, program kerja]
        $base = [
            ['Tonasa 4', 'Seksi Pemeliharaan EP/DC', 'M01', '1000293841', 'BEARING BALL 6205 C3 SKF', 24, 'PCE', 125000, 'OK', true, 'R01', 'ST-4 REPLACE BEARING BALL EP 4'],
            ['Tonasa 5', 'Seksi. Operasi FM 2/3', 'M02', '1000293845', 'OIL SEAL 45x62x8 NBR', 10, 'PCE', 85000, 'NO', false, 'R02', 'ST-5 REPLACE OIL SEAL FM 5'],
            ['Tonasa 2', 'Seksi. Operasi FM 4', 'M01', '1000301120', 'FIREBRICKS TYPE AL-60 HIGH ALUMINA', 500, 'PCS', 45000, 'OK', true, 'R01', 'ST-2 REPLACE FIREBRICKS KILN 2'],
            ['Tonasa 3', 'Seksi. Operasi RKC 4', 'M03', '1000305582', 'CASTABLE DENSE C-16 SUPER', 120, 'BAG', 320000, 'NO', false, 'R03', 'ST-3 REPAIR CASTABLE COOLER 3'],
            ['Power Plant', 'Seksi. Operasi FM 5', 'M01', '1000312009', 'ANCHOR V-TYPE STAINLESS STEEL 310', 1500, 'PCS', 18500, 'OK', true, 'R01', 'PP REPLACE ANCHOR BOILER'],
            ['Tonasa 5', 'Seksi. Pemeliharaan RKC 4/5', 'M02', '1000318742', 'V-BELT B-85 GATES SUPER HC', 16, 'PCE', 140000, 'OK', false, 'R02', 'ST-5 REPLACE V-BELT FAN RKC 5'],
            ['Tonasa 4', 'Seksi Pemeliharaan Listrik', 'M01', '1000320011', 'CABLE NYY 4x16 MM2 0,6/1KV', 250, 'MTR', 110000, 'NO', false, 'R01', 'ST-4 REPLACE CABLE MOTOR KILN 4'],
            ['Tonasa 3', 'Seksi. Operasi Crusher', 'M02', '1000324490', 'HAMMER CRUSHER HIGH MANGANESE STEEL', 12, 'PCE', 4500000, 'OK', true, 'R02', 'ST-3 REPLACE HAMMER CRUSHER 3'],
        ];

        $statuses = [
            'Lanjut', 'Pending', 'Lanjut', 'Batal', 'Lanjut', 'Lanjut', 'Pending', 'Lanjut',
            'Lanjut', 'Batal', 'Lanjut', 'Pending', 'Lanjut', 'Batal', 'Lanjut', 'Pending',
            'Pending', 'Lanjut', 'Lanjut', 'Batal', 'Lanjut', 'Lanjut', 'Batal', 'Lanjut',
        ];
        $categories = ['Critical Sparepart', 'Consumable', 'Capital Spare', 'Insurance Spare'];
        $evaluators = ['Rahmat A.', 'Sari W.', 'Budi S.', 'Amir F.'];

        $rows = [];
        foreach ($statuses as $index => $status) {
            [$plant, $unit, $mrp, $materialNo, $shortText, $qty, $uom, $price, $bom, $hasDrawing, $rif, $program] = $base[$index % count($base)];
            $variant = intdiv($index, count($base));
            if ($variant > 0) {
                $materialNo = (string) ((int) $materialNo + 13 * $variant + $index);
            }
            $cancelled = $status === 'Batal';

            $rows[] = [
                'id' => 'sc-'.($index + 1),
                'year' => 2026,
                'plant' => $plant,
                'unit' => $unit,
                'status' => $status,
                'bom_status' => $cancelled ? 'NO' : $bom,
                'mrp_type' => $mrp,
                'material_no' => $materialNo,
                'short_text' => $shortText,
                'qty' => $qty,
                'uom' => $uom,
                'price' => $price,
                'drawing' => $hasDrawing ? 'Drawing_v'.(($index % 3) + 1).'.pdf' : '-',
                'rif' => $rif,
                'plant_section' => $plant,
                'program' => $program,
                'equipment_id' => 'EQ-T'.substr($plant, -1).'-'.(9982 + $index),
                'qty_bom' => $qty,
                'uom_bom' => $uom,
                'lifetime' => '12/01/2026 - 12/12/2026',
                'usage_plan' => 'Q'.(($index % 4) + 1).' 2026',
                'notification' => 'NOT-'.(8810 + $index),
                'order' => 'ORD-'.(9921 + $index),
                'reservation' => 'RES-'.str_pad((string) (12 + $index), 4, '0', STR_PAD_LEFT),
                'mat_group' => 'MG-0'.(($index % 5) + 1),
                'mat_padanan' => 'ALT-'.(6205 + $index),
                'long_text' => $shortText.'. Spesifikasi mengikuti standar pabrikan dan hasil evaluasi Tim Overhaul untuk kebutuhan capex tahun 2026.',
                'delivery' => ($index % 4 + 2).' Bulan',
                'warranty' => 12,
                'warranty_type' => 'Pabrikan',
                'coo' => 'Doc_Sertifikat.pdf',
                'link' => 'https://vendor.co.id/item/'.($index + 1),
                'po_ref' => 'PO-2025-'.(881902 + $index),
                'po_date' => '2025-01-12',
                'category' => $categories[$index % count($categories)],
                'material_category' => 'OEM '.($index % 2 === 0 ? 'SKF Sweden' : 'Siemens'),
                'evaluation_note' => 'Spesifikasi material sudah sesuai dengan standar pabrikan '.$plant.'. Stok aman dan vendor menyetujui jadwal pengiriman Q3 2026.',
                'qty_approved' => $cancelled ? 0 : $qty,
                'pic' => $evaluators[$index % count($evaluators)],
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
            ['id' => 'seed-1', 'date' => '2026-10-20', 'status' => 'Selesai', 'text' => 'Evaluasi spesifikasi Bearing Ball 6205 C3 SKF Tonasa 4, status BOM OK dan disetujui untuk pembelian Q3 2026.'],
            ['id' => 'seed-2', 'date' => '2026-10-21', 'status' => 'In Progress', 'text' => 'Koordinasi vendor pengadaan material Oil Seal Tonasa 5, menunggu konfirmasi ketersediaan stok dan jadwal pengiriman.'],
            ['id' => 'seed-3', 'date' => '2026-10-22', 'status' => 'Penting', 'text' => 'Audit pembatalan item material (Cancel) seksi operasi RKC 5, menunggu persetujuan atasan terkait efisiensi anggaran.'],
        ];

        return $index === 0 ? $all : [$all[min($index, 2)]];
    }
}
