<?php

namespace App\Support;

/**
 * Data dummy untuk tampilan Rencana Kerja Barang (frontend saja).
 * Ganti isi make() dengan data dari database saat backend siap; bentuk array dipakai langsung oleh
 * view modules.schedule-barang.* dan public/assets/js/schedule-barang.js.
 */
class ScheduleBarangPageData
{
    public const STATUS_APPROVED = 'Lanjut';

    public const STATUS_PENDING = 'Pending';

    public const STATUS_CANCELLED = 'Batal';

    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        $rows = self::rows();

        return [
            'updatedAt' => '12/11/2026, 15:40',
            'years' => [2026, 2027, 2025],
            'plants' => ['Tonasa 2', 'Tonasa 3', 'Tonasa 4', 'Tonasa 5', 'Power Plant'],
            'budget' => [
                ['label' => 'Rencana Anggaran', 'value' => '230,5', 'unit' => 'Miliar'],
                ['label' => 'Total Ajuan', 'value' => '350,5', 'unit' => 'Miliar'],
                ['label' => 'Total Disetujui', 'value' => '220,5', 'unit' => 'Miliar'],
            ],
            'approvedPercent' => 96,
            'items' => [
                ['label' => 'Total Ajuan', 'value' => '1800', 'icon' => 'check'],
                ['label' => 'Disetujui', 'value' => '1300', 'icon' => 'check'],
                ['label' => 'Dibatalkan', 'value' => '500', 'icon' => 'x'],
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
            'rows' => $rows,
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
        // [plant, unit kerja, deskripsi proyek, no. akun, spesifikasi, qty, uom, harga unit]
        $base = [
            ['Tonasa 2', 'SEKSI PEMEL. MESIN FM 2/3', 'ST-3 REPLACE LINING SEPARATOR FM 3', '5308021450', 'LINING,SEPARATOR,HRC600,2200MM,SS304', 2, 'UNIT', 845000000],
            ['Tonasa 2', 'SEKSI PEMEL. ELINS FM 2/3', 'ST-2 REPLACE VFD PANEL FM 2 MOTOR', '5308018732', 'VFD,PANEL,690V,400KW,IP54,ABB', 1, 'UNIT', 1240500000],
            ['Tonasa 2', 'SEKSI PEMEL. EP/DC', 'ST-2 REPLACE DISCHARGE ELECTRODE EP 212', '5308027115', 'ELECTRODE,DISCHARGE,SS316,L4500MM', 12, 'EA', 36750000],
            ['Tonasa 5', 'SEKSI PEMEL. INSTR. RKC 4/5', 'ST-5 REPLACE LEVEL TRANSMITTER KILN FEED', '5308033488', 'TRANSMITTER,LEVEL,RADAR,4-20MA,80GHZ', 4, 'UNIT', 62300000],
            ['Tonasa 4', 'SEKSI PEMEL. LISTRIK RKC 4/5', 'ST-4 REPLACE MOTOR KILN ID FAN 421', '5308028977', 'MOTOR,AC,6600V,1250KW,IP55,1480RPM', 1, 'UNIT', 2150000000],
            ['Tonasa 4', 'SEKSI PEMEL. MESIN CRUSHER', 'ST-4 REPLACE HAMMER CRUSHER LIMESTONE', '5308016980', 'HAMMER,CRUSHER,HIGH CR,L1200MM,42KG', 24, 'EA', 18900000],
            ['Tonasa 2', 'SEKSI PEMEL. MESIN FM 2/3', 'ST-3 REPLACE GRINDING MEDIA BALL FM 3', '5308019233', 'BALL,GRINDING,CHROME,DIA 60MM,CR18', 3, 'SET', 1480000000],
            ['Tonasa 5', 'SEKSI PEMEL. MESIN FM 4/5', 'ST-5 REPLACE DIAPHRAGM SLOT PLATE FM 5', '5308036402', 'PLATE,SLOT,DIAPHRAGM,SS304,T12', 2, 'UNIT', 735200000],
            ['Tonasa 4', 'SEKSI PEMEL. MESIN RM 4/5', 'ST-4 REPLACE GRINDING ROLLER RM 421', '5308024559', 'ROLLER,GRINDING,HARDFACING,DIA 2400MM', 2, 'UNIT', 3280000000],
            ['Tonasa 4', 'SEKSI OPERASI FM4', 'ST-4 REPLACE FEEDER FLYASH FM 4', '5308012902', 'ROTARY,FEEDER,PA,25,TO,W/D,380,IP55', 1, 'UNIT', 585000000],
            ['Tonasa 4', 'SEKSI OPERASI RKC 4', 'ST-4 REPLACE KILN INLET SEAL RKC 4', '5308034776', 'SEAL,KILN,INLET,LEAF,INCONEL,D5000', 1, 'SET', 1915200000],
            ['Tonasa 3', 'SEKSI BENGKEL MESIN', 'ST-3 SUPPLY WELDING MACHINE WORKSHOP', '5308030661', 'WELDING,MACHINE,MIG,500A,380V', 5, 'UNIT', 48600000],
            ['Tonasa 5', 'SEKSI PEMEL. ELINS FM 4/5', 'ST-5 REPLACE PLC CPU PM 5 CONTROL', '5308037215', 'PLC,CPU,REDUNDANT,SIEMENS,S7-1500', 2, 'UNIT', 295000000],
            ['Tonasa 4', 'SEKSI PEMEL. MESIN KCM 4/5', 'ST-4 REPLACE BELT BUCKET ELEVATOR KCM 5', '5308035844', 'BELT,ELEV,1450MM,T200,BUCKET,BEUMER', 1, 'UNIT', 6023059000],
            ['Tonasa 5', 'SEKSI OPERASI FM 5', 'ST-5 REPLACE FEEDER FLYASH CM 5T', '5308027001', 'ROTARY,FEEDER,PA,35,TO,W/D,FMCP,380,IP65', 1, 'UNIT', 1230000000],
        ];

        $statuses = [
            'Lanjut', 'Pending', 'Pending', 'Batal', 'Lanjut', 'Lanjut', 'Lanjut', 'Batal', 'Lanjut', 'Pending', 'Lanjut', 'Batal', 'Pending', 'Lanjut', 'Lanjut',
            'Lanjut', 'Batal', 'Lanjut', 'Pending', 'Lanjut', 'Batal', 'Lanjut', 'Lanjut', 'Pending', 'Batal', 'Lanjut', 'Lanjut', 'Batal', 'Lanjut', 'Pending',
        ];
        $categories = ['Critical Sparepart', 'Consumable', 'Capital Spare', 'Insurance Spare'];
        $evaluators = ['Rahmat A.', 'Sari W.', 'Budi S.', 'Amir F.'];

        $rows = [];
        foreach ($statuses as $index => $status) {
            [$plant, $unit, $project, $account, $spec, $qty, $uom, $price] = $base[$index % count($base)];
            $variant = intdiv($index, count($base));
            if ($variant > 0) {
                $account = (string) ((int) $account + 1000 * $variant + $index);
                $qty += $variant;
            }

            $rows[] = [
                'id' => 'sb-'.($index + 1),
                'year' => 2026,
                'plant' => $plant,
                'unit' => $unit,
                'project' => $project,
                'account' => $account,
                'spec' => $spec,
                'qty' => $qty,
                'uom' => $uom,
                'price' => $price,
                'status' => $status,
                'plant_section' => $plant,
                'program' => $project,
                'equipment_id' => 'EQ-T'.substr($plant, -1).'-'.(9982 + $index),
                'qty_bom' => $qty,
                'uom_bom' => $uom === 'EA' ? 'PCE' : $uom,
                'mrp_type' => 'M0'.(($index % 3) + 1),
                'lifetime' => '12/01/2026 - 12/12/2026',
                'usage_plan' => 'Q'.(($index % 4) + 1).' 2026',
                'notification' => 'NOT-'.(8810 + $index),
                'order' => 'ORD-'.(9921 + $index),
                'reservation' => 'RES-'.str_pad((string) (12 + $index), 4, '0', STR_PAD_LEFT),
                'mat_group' => 'MG-0'.(($index % 5) + 1),
                'material_no' => (string) (1000293841 + $index * 7),
                'mat_padanan' => 'ALT-'.(6205 + $index),
                'long_text' => $spec.'. Spesifikasi mengikuti standar pabrikan dan hasil evaluasi Tim Overhaul untuk kebutuhan overhaul tahun 2026.',
                'short_text' => $spec,
                'delivery' => ($index % 4 + 2).' Bulan',
                'warranty' => 12,
                'warranty_type' => 'Pabrikan',
                'coo' => 'Doc_Sertifikat.pdf',
                'drawing' => 'Drawing_v'.(($index % 3) + 1).'.dwg',
                'link' => 'https://vendor.co.id/item/'.($index + 1),
                'po_ref' => 'PO-2025-'.(881902 + $index),
                'po_date' => '2025-01-12',
                'category' => $categories[$index % count($categories)],
                'material_category' => 'OEM '.($index % 2 === 0 ? 'SKF Sweden' : 'Siemens'),
                'evaluation_note' => 'Spesifikasi material sudah sesuai dengan standar pabrikan '.$plant.'. Stok aman dan vendor menyetujui jadwal pengiriman Q3 2026.',
                'qty_approved' => $status === 'Batal' ? 0 : $qty,
                'bom_status' => $status === 'Batal' ? 'NO' : 'OK',
                'pic' => $evaluators[$index % count($evaluators)],
                'notes' => $index < 3 ? self::seedNotes($index) : [],
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

        return $index === 0 ? $all : [$all[$index]];
    }
}
