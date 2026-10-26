@php
    $procurementGoodsRows = [
        ['Tahap 1', 'Tonasa 4', 'Seksi Pemeliharaan RKC 4/5', '5100016978', 'INSULATION MAT; ALUM CERMIC; AWG 2; 12MMX50MM', '2300015224'],
        ['Tahap 1', 'Tonasa 5', 'Seksi Pemeliharaan RKC 4/5', '5100046147', 'THERMOWELLS; TEMP; 22; 1000; MKL-000557', '-'],
        ['Tahap 2', 'Tonasa 4', 'Seksi Pemeliharaan RKC 4/5', '5100017213', 'SENSOR; THERMOCOUPLE; ELEMENT TYPE K; AWG8', '2300025455'],
        ['Tahap 2', 'Tonasa 4', 'Seksi Pemeliharaan RKC 4/5', '5100016979', 'INSULATION MAT; ALUM CERMIC; AWG 8; 3.2MM', '2300020480'],
        ['Tahap 1', 'Tonasa 4', 'Seksi Pemeliharaan RKC 4/5', '5100016978', 'INSULATION MAT; ALUM CERMIC; AWG 2; 12MMX50MM', '2300015224'],
        ['Tahap 1', 'Tonasa 5', 'Seksi Pemeliharaan RKC 4/5', '5100046147', 'THERMOWELLS; TEMP; 22; 1000; MKL-000557', '2300015224'],
        ['Tahap 2', 'Tonasa 4', 'Seksi Pemeliharaan RKC 4/5', '5100017213', 'SENSOR; THERMOCOUPLE; ELEMENT TYPE K; AWG8', '2300025455'],
        ['Tahap 2', 'Tonasa 4', 'Seksi Pemeliharaan RKC 4/5', '5100016979', 'INSULATION MAT; ALUM CERMIC; AWG 8; 3.2MM', '2300020480'],
        ['Tahap 1', 'Tonasa 5', 'Seksi Operasi Finish Mill 4/5', '5100082311', 'BEARING BALL; 6205 C3 SKF', '2300031588'],
        ['Tahap 2', 'Tonasa 3', 'Seksi Pemeliharaan Crusher', '5100075402', 'BELT CONVEYOR; EP500; 1000MM', '2300031042'],
        ['Tahap 1', 'Tonasa 2', 'Seksi Pemeliharaan Raw Mill', '5100067720', 'OIL SEAL; VITON; 85X110X12', '2300029980'],
        ['Tahap 2', 'Tonasa 5', 'Seksi Pemeliharaan Coal Mill', '5100091455', 'FILTER BAG; POLYESTER; 160X6000MM', '2300034105'],
        ['Tahap 1', 'Tonasa 4', 'Seksi Electrical Maintenance', '5100056824', 'CONTACTOR; 3 POLE; 220VAC; 95A', '2300027731'],
        ['Tahap 2', 'Tonasa 3', 'Seksi Instrumentation', '5100039248', 'TRANSMITTER; PRESSURE; 4-20MA', '2300026028'],
        ['Tahap 1', 'Tonasa 5', 'Seksi Pemeliharaan Kiln', '5100084921', 'REFRACTORY BRICK; SK-34; 230X114X65', '2300033221'],
        ['Tahap 2', 'Tonasa 2', 'Seksi Packing Plant', '5100061540', 'SOLENOID VALVE; 24VDC; 1/2 INCH', '2300028804'],
        ['Tahap 1', 'Tonasa 4', 'Seksi Mechanical Workshop', '5100070163', 'COUPLING; FLEXIBLE; TYPE HRC 180', '2300030187'],
        ['Tahap 2', 'Tonasa 5', 'Seksi Power Distribution', '5100095277', 'CIRCUIT BREAKER; 400A; 3 POLE', '2300035064'],
    ];

    $modeDefinitions = [
        'pengeluaran' => ['label' => 'Pengeluaran Barang', 'tone' => 'green', 'procurement' => 'Complete Kontrak / PO', 'progress' => 100, 'po' => '5000011474'],
        'siap' => ['label' => 'Siap', 'tone' => 'teal', 'procurement' => 'Complete Kontrak / PO', 'progress' => 100, 'po' => '5000011474'],
        'pengiriman' => ['label' => 'Pengiriman', 'tone' => 'blue', 'procurement' => 'Complete Kontrak / PO', 'progress' => 100, 'po' => '5000011474'],
        'pengadaan' => ['label' => 'Pengadaan', 'tone' => 'navy', 'procurement' => 'Proses Penawaran', 'progress' => 25, 'po' => '-'],
        'dokumen' => ['label' => 'Dokumen & PP', 'tone' => 'purple', 'procurement' => 'Dokumen & Permintaan Pembelian', 'progress' => 15, 'po' => '-'],
        'tertunda' => ['label' => 'Pending', 'tone' => 'amber', 'procurement' => 'Berita acara persetujuan', 'progress' => 5, 'po' => '-'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'tone' => 'red', 'procurement' => 'Dibatalkan / Cancel', 'progress' => 0, 'po' => '-'],
        'semua' => ['label' => 'Semua', 'tone' => 'navy', 'procurement' => 'Proses Pengadaan', 'progress' => 25, 'po' => '-'],
    ];
    $mode = $modeDefinitions[$listMode] ?? $modeDefinitions['semua'];
    $procurementStages = ['Berita Acara Persetujuan', 'Proses Dokumen Tender', 'Verifikasi Pengadaan', 'Penetapan Panitia Pengadaan', 'RKS / HPS', 'Daftar Peserta Tender', 'Proses Penawaran', 'Evaluasi Teknis', 'Negosiasi', 'Penetapan Pemenang', 'Pengumuman Pemenang', 'Pemberitahuan Pemenang', 'Pesanan Pembelian (PO)'];
    $mixedStatuses = [
        ['Pengadaan', 'navy', 5], ['Pengadaan', 'navy', 10], ['Pengadaan', 'navy', 15], ['Pengadaan', 'navy', 20],
        ['Pengadaan', 'navy', 25], ['Pengadaan', 'navy', 30], ['Pengadaan', 'navy', 35], ['Pengadaan', 'navy', 40],
        ['Pengadaan', 'navy', 50], ['Pengadaan', 'navy', 60], ['Pengadaan', 'navy', 70], ['Pengadaan', 'navy', 80],
        ['Pengiriman', 'blue', 100], ['Tertunda', 'amber', 5], ['Dibatalkan', 'red', 0], ['Pengadaan', 'navy', 10],
        ['Siap', 'teal', 100], ['Barang Keluar', 'green', 100],
    ];
    $visibleRows = $listMode === 'semua' ? $procurementGoodsRows : array_slice($procurementGoodsRows, 0, 8);
@endphp

<section class="procurement-goods-filter is-list" aria-label="Filter daftar pengadaan barang">
    <label><span>Tahun</span><select data-procurement-year><option selected>2026</option><option>2027</option></select></label>
    <label><span>Plant</span><select data-procurement-plant><option value="all" selected>Semua Plant</option><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option>Tonasa 5</option></select></label>
    <label class="procurement-goods-search"><span>Search</span><div><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Unit kerja / spesifikasi / MRP" data-procurement-search></div></label>
</section>

<section class="procurement-goods-table-card" data-procurement-table>
    <div class="procurement-goods-table-scroll">
        <table>
            <thead>
                <tr>
                    <th>No.</th><th>Ajuan</th><th>Pabrik</th><th>Unit Kerja</th><th>No. Item</th><th>Spesifikasi (Short Text)</th><th>Nomor PR</th><th>Status Pengadaan</th><th>Progress</th><th>Nomor PO</th><th>Status Permintaan</th><th>Edit</th><th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($visibleRows as $index => $row)
                    @php
                        [$stage, $plant, $unit, $itemNumber, $specification, $prNumber] = $row;
                        $mixed = $mixedStatuses[$index] ?? $mixedStatuses[0];
                        $statusLabel = $listMode === 'semua' ? $mixed[0] : $mode['label'];
                        $statusTone = $listMode === 'semua' ? $mixed[1] : $mode['tone'];
                        $progress = $listMode === 'semua' ? $mixed[2] : min(100, $mode['progress'] + (($listMode === 'pengadaan' || $listMode === 'dokumen') ? ($index * 9) : 0));
                        $procurementStatus = $listMode === 'semua' ? ($procurementStages[$index % count($procurementStages)] ?? $mode['procurement']) : (($listMode === 'pengadaan') ? $procurementStages[$index % count($procurementStages)] : $mode['procurement']);
                        $poNumber = in_array($statusTone, ['green', 'teal', 'blue'], true) ? '5000011474' : $mode['po'];
                        $searchValue = strtolower(implode(' ', [$stage, $plant, $unit, $itemNumber, $specification, $prNumber, $procurementStatus, $statusLabel]));
                    @endphp
                    <tr data-procurement-row data-plant="{{ $plant }}" data-search="{{ $searchValue }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $stage }}</td>
                        <td><strong>{{ $plant }}</strong></td>
                        <td>{{ $unit }}</td>
                        <td><strong>{{ $itemNumber }}</strong></td>
                        <td><strong>{{ $specification }}</strong></td>
                        <td>{{ $prNumber }}</td>
                        <td>{{ $procurementStatus }}</td>
                        <td><strong>{{ $progress }}%</strong></td>
                        <td>{{ $poNumber }}</td>
                        <td><span class="procurement-goods-status is-{{ $statusTone }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $statusLabel }}</span></td>
                        <td><button type="button" class="procurement-goods-action is-edit" title="Edit pengadaan" aria-label="Edit pengadaan baris {{ $index + 1 }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></button></td>
                        <td><button type="button" class="procurement-goods-action is-note" title="Lihat catatan" aria-label="Lihat catatan baris {{ $index + 1 }}"><i class="bi bi-chat-left-text" aria-hidden="true"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="procurement-goods-empty" data-procurement-empty hidden><i class="bi bi-search" aria-hidden="true"></i><span>Data pengadaan tidak ditemukan.</span></div>
    <footer class="procurement-goods-table-footer">
        <span data-procurement-summary aria-live="polite"></span>
        <nav data-procurement-pagination aria-label="Pagination daftar pengadaan barang"></nav>
    </footer>
</section>
