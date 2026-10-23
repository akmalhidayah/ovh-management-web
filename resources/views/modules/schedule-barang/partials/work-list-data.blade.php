@php
    // Reference data for the frontend preview only; no database records are changed.
    $workListReference = [
        ['300001942331', 'JASA GANTI CHAIN REKLAIMER', 7, 1, 'LOT', 65000000],
        ['300001942333', 'JASA GANTI LAMELLA APRON FEEDER', 5, 1, 'LOT', 70000000],
        ['300001942348', 'GANTI TYRE ROLLER 553, SLEDING MATERIAL MAHKOTA MROLLER', 12, 1, 'LOT', 301288182],
        ['300001942372', 'JASA GANTI SAMBUNGAN BELT BUCKET', 3, 1, 'LOT', 65000000],
        ['300001942569', 'JASA GANTI FEEDER SET', 10, 1, 'LOT', 145000000],
        ['300001942569', 'JASA BOBOL DINDING FEEDER DAN PASANG DINDING', 5, 1, 'LOT', 45000000],
        ['300001942484', 'JASA REPAIR INNERPART MILL (MAHKOTA MROLLER DAN BODY MILL)', 10, 1, 'LOT', 66000000],
        ['300001942589', 'JASA GANTI SAMBUNGAN HOT SPLICING', 3, 1, 'LOT', 21520000],
        ['300001942626', 'JASA GANTI CHUTE SLIDE GATE (4 SLIDE GATE)', 4, 1, 'LOT', 67070122],
        ['300001942420', 'JASA SERVICE COOLER', 5, 1, 'LOT', 195848750],
        ['300001942685', 'JASA GANTI HULK MROLLER 03', 4, 1, 'LOT', 109200000],
        ['300001942699', 'JASA GANTI HULK MROLLER 01', 4, 1, 'LOT', 109200000],
        ['300001943939', 'JASA LINING HIGH ALUMINA CERAMIC TILES FM 552', 5, 10, 'M2', 13000000],
        ['300001943947', 'JASA LINING HIGH ALUMINA CERAMIC TILES FM 553', 5, 7, 'M2', 13000000],
        ['300001945651', 'JASA REBRICKING KILN TONASA 5 (86 METER)', 18, 1, 'LOT', 895580720],
        ['300001945658', 'JASA INSTALASI LINING WEAR RESISTANCE COAL MILL, COOLER & RAW MILL 5', 10, 1, 'LOT', 599100000],
        ['300001945697', 'JASA PENGGANTIAN / PEMASANGAN ISOLASI THERMAL HOTGAS RAW MILL', 10, 400, 'M2', 850000],
        ['', 'JASA INSTALASI JALUR INSPEKSI TAD KILN TONASA 5 (TAHAP 2)', 15, 1, 'LOT', 0],
        ['', 'JASA PEREMAJAAN CYCLONE COOLER 5', 15, 1, 'LOT', 0],
    ];
    $workListInternalStatuses = ['PGO', 'TS', 'BMS', 'PGO', 'TS', 'PGO', 'BMS', 'PGO', 'TS', 'PGO', 'BMS', 'PGO', 'TS', 'PGO', 'TS', 'BMS', 'PGO'];
    $workListAllStatuses = ['Lanjut', 'PGO', 'BMS', 'TS', 'Lanjut', 'PGO', 'BMS', 'Lanjut', 'TS', 'PGO', 'BMS', 'Lanjut', 'TS', 'PGO', 'TS', 'BMS', 'PGO', 'TS', 'Lanjut'];
    $workListProcesses = ['Kontrak', 'Fabrikasi', 'Tender', 'Cancel', 'PO/Done', 'Kontrak', 'BMS Review', 'Fabrikasi', 'Tender', 'PO/Done', 'BMS Review', 'Kontrak', 'Tender', 'Fabrikasi', 'Tender', 'BMS Review', 'PO/Done', 'Tender', 'Kontrak'];
    $workListRows = [];
    foreach ($workListReference as $index => [$notification, $description, $duration, $quantity, $uom, $price]) {
        if (in_array($listMode, ['lanjut', 'internal'], true) && $index >= 17) {
            continue;
        }
        $status = match ($listMode) {
            'lanjut' => 'Lanjut',
            'internal' => $workListInternalStatuses[$index],
            'batal' => 'Cancel',
            default => $workListAllStatuses[$index],
        };
        $workListRows[] = [
            'id' => 'barang-reference-'.($index + 1),
            'year' => 2027,
            'unit' => $index < 12 ? 'Seksi Pemel. Mesin FM 4/5' : ($index < 14 ? 'Seksi Operasi FM 5' : 'Seksi Operasi RKC 5'),
            'plant' => 'Tonasa 5',
            'area' => $index < 14 ? 'Finish Mill 5' : 'Kiln 5',
            'location' => '',
            'equipment' => '',
            'notification' => $notification,
            'description' => 'OH27 - '.$description,
            'duration' => $duration,
            'quantity' => $quantity,
            'uom' => $uom,
            'price' => $listMode === 'batal' ? 0 : $price,
            'status' => $status,
            'process' => $listMode === 'batal' ? 'Cancel' : $workListProcesses[$index],
            'notes' => [],
        ];
    }
@endphp
