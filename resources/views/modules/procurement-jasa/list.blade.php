@php
    $procurementServiceRows = [
        ['Tonasa 4', 'Seksi Pemeliharaan EP/Crusher', '300001949749', 'OH27 - JASA PURIFIKASI OLI TRAFO 54 MVA', '876.480.000', '8600018202'],
        ['Tonasa 5', 'Seksi Operasi FM 2/3', '300001943224', 'OH27 - JASA TENAGA NON SKILL OVERHAUL FINISH MILL', '46.800.000', '8600017917'],
        ['Tonasa 2', 'Seksi Operasi FM 4', '300001943308', 'OH27 - JASA PEMBERSIHAN ATAP AREA FINISH MILL', '23.400.000', '8600017915'],
        ['Tonasa 3', 'Seksi Operasi RKC 4', '300001942353', 'JASA TENAGA HELPER NON SKILL KILN OPERATION', '118.560.000', '8600017751'],
        ['Power Plant', 'Seksi Operasi FM 5', '300001943655', 'OH27 - JASA NON-SKILL OPERASI KILN DAN COOLER', '31.200.000', '8600018203'],
        ['Tonasa 5', 'Seksi Operasi RKC 5', '300001945629', 'JASA HELPER NON-SKILL OPERASI RAW MILL', '68.641.760', '8600018222'],
        ['Tonasa 4', 'Seksi Pemeliharaan Kiln', '300001946112', 'OH27 - JASA ALIGNMENT GIRTH GEAR KILN', '425.000.000', '8600018240'],
        ['Tonasa 3', 'Seksi Pemeliharaan Raw Mill', '300001946250', 'OH27 - OVERHAUL GEARBOX UTAMA RAW MILL', '535.500.000', '8600018251'],
        ['Tonasa 2', 'Seksi Crusher', '300001946408', 'OH27 - MAJOR INSPECTION PRIMARY CRUSHER', '286.750.000', '8600018266'],
        ['Tonasa 5', 'Seksi Coal Mill', '300001946577', 'OH27 - DYNAMIC BALANCING COAL MILL FAN', '198.400.000', '8600018274'],
        ['Tonasa 4', 'Seksi Clinker Cooler', '300001946711', 'OH27 - PENGGANTIAN COOLER PLATE', '342.600.000', '8600018283'],
        ['Tonasa 3', 'Seksi Preheater', '300001946895', 'OH27 - CLEANING CYCLONE DAN DUCTING', '176.850.000', '8600018291'],
        ['Tonasa 5', 'Seksi Cement Mill', '300001947020', 'OH27 - NDT SHELL DAN TRUNNION', '245.300.000', '8600018305'],
        ['Tonasa 2', 'Seksi Packing Plant', '300001947164', 'OH27 - KALIBRASI PACKER DAN LOAD CELL', '92.750.000', '8600018317'],
        ['Tonasa 4', 'Seksi ID Fan', '300001947322', 'OH27 - REPAIR IMPELLER ID FAN', '310.500.000', '8600018330'],
        ['Tonasa 3', 'Seksi Belt Conveyor', '300001947488', 'OH27 - HOT SPLICING BELT CONVEYOR', '154.250.000', '8600018342'],
        ['Tonasa 5', 'Seksi Silo', '300001947605', 'OH27 - INSPEKSI STRUKTUR SILO', '127.800.000', '8600018356'],
        ['Tonasa 4', 'Seksi Power House', '300001947731', 'OH27 - MAJOR SERVICE GENERATOR', '468.900.000', '8600018364'],
    ];
    $serviceModes = [
        'finish' => ['label' => 'FINISH', 'tone' => 'green', 'progress' => 100, 'process' => 'Kontrak/Purchase Order', 'status' => 'Kontrak/Purchase Order', 'po' => true],
        'ready' => ['label' => 'Ready', 'tone' => 'teal', 'progress' => 100, 'process' => 'Kontrak/Purchase Order', 'status' => 'Kontrak/Purchase Order', 'po' => true],
        'procurement' => ['label' => 'PROSES PENGADAAN', 'tone' => 'blue', 'progress' => 17, 'process' => 'Kontrak Tenaga Non Skill', 'status' => 'Proses dokumen tender', 'po' => false],
        'pending' => ['label' => 'PENDING', 'tone' => 'amber', 'progress' => 22, 'process' => 'Kontrak Tenaga Non Skill', 'status' => 'Persetujuan dokumen', 'po' => false],
        'cancel' => ['label' => 'CANCEL', 'tone' => 'red', 'progress' => 0, 'process' => 'Kontrak Tenaga Non Skill', 'status' => 'CANCEL', 'po' => false],
        'all' => ['label' => 'All Item', 'tone' => 'navy', 'progress' => 45, 'process' => 'Kontrak Tenaga Non Skill', 'status' => 'Proses Pengadaan', 'po' => false],
    ];
    $serviceMode = $serviceModes[$listMode] ?? $serviceModes['all'];
    $serviceProcurementStages = ['Proses dokumen tender', 'Persetujuan dokumen', 'RFC & Proses Negosiasi', 'Usulan persetujuan pemenang', 'Pengumuman pemenang'];
    $serviceMixedStatuses = [
        ['Pending', 'amber', 100], ['Ready', 'teal', 100], ['Finish', 'green', 100],
        ['Proses Pengadaan', 'blue', 45], ['Proses Pengadaan', 'blue', 75], ['Cancel', 'red', 0],
        ['Ready', 'teal', 100], ['Pending', 'amber', 65], ['Finish', 'green', 100],
        ['Proses Pengadaan', 'blue', 22], ['Cancel', 'red', 0], ['Ready', 'teal', 100],
        ['Finish', 'green', 100], ['Pending', 'amber', 45], ['Proses Pengadaan', 'blue', 75],
        ['Ready', 'teal', 100], ['Cancel', 'red', 0], ['Finish', 'green', 100],
    ];
    $visibleServiceRows = $listMode === 'all' ? $procurementServiceRows : array_slice($procurementServiceRows, 0, 6);
@endphp

<section class="procurement-goods-filter procurement-service-filter" aria-label="Filter daftar pengadaan jasa">
    <label><span>Tahun</span><select><option selected>2026</option><option>2027</option></select></label>
    <label><span>Plant</span><select data-procurement-plant><option value="all" selected>Semua Plant</option><option>Tonasa 2</option><option>Tonasa 3</option><option>Tonasa 4</option><option>Tonasa 5</option><option>Power Plant</option></select></label>
    <label class="procurement-goods-search"><span>Cari Unit Kerja/PIC</span><div><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Search unit kerja/PIC..." data-procurement-search></div></label>
    <label class="procurement-goods-search"><span>Spesifikasi</span><div><i class="bi bi-card-text" aria-hidden="true"></i><input type="search" placeholder="Spesifikasi..." data-procurement-specification></div></label>
    <div class="procurement-service-export-actions">
        <button type="button" class="is-excel"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Export Excel</button>
        <button type="button" class="is-pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Download to PDF</button>
    </div>
</section>

<section class="procurement-goods-table-card procurement-service-table" data-procurement-table>
    <div class="procurement-goods-table-scroll">
        <table>
            <thead>
                <tr><th>Plant</th><th>Unit Kerja</th><th>Notifikasi</th><th>Deskripsi Pekerjaan</th><th>Total Harga</th><th>Proses Pengadaan</th><th>Status Pengadaan</th><th>Progress</th><th>No. PO</th><th>Batas</th><th>Status Permintaan</th><th>Edit</th><th>Note</th></tr>
            </thead>
            <tbody>
                @foreach ($visibleServiceRows as $index => $row)
                    @php
                        [$plant, $unit, $notification, $description, $total, $poNumber] = $row;
                        $mixedStatus = $serviceMixedStatuses[$index] ?? $serviceMixedStatuses[0];
                        $requestStatus = $listMode === 'all' ? $mixedStatus[0] : $serviceMode['label'];
                        $statusTone = $listMode === 'all' ? $mixedStatus[1] : $serviceMode['tone'];
                        $progressValues = [17, 22, 45, 65, 75, 100];
                        $progress = $listMode === 'all' ? $mixedStatus[2] : (($listMode === 'procurement' || $listMode === 'pending') ? $progressValues[$index % count($progressValues)] : $serviceMode['progress']);
                        $procurementStatus = $listMode === 'procurement' ? $serviceProcurementStages[$index % count($serviceProcurementStages)] : $serviceMode['status'];
                        $resolvedPo = ($serviceMode['po'] || in_array($statusTone, ['green', 'teal'], true)) ? $poNumber : '-';
                        $deadline = in_array($statusTone, ['green', 'teal', 'amber'], true) ? ['05/06/2026', '31/05/2026', '04/06/2026'][$index % 3] : '-';
                        $searchValue = strtolower(implode(' ', [$plant, $unit, $notification, $description, $total, $serviceMode['process'], $procurementStatus, $requestStatus]));
                    @endphp
                    <tr data-procurement-row data-plant="{{ $plant }}" data-search="{{ $searchValue }}" data-specification="{{ strtolower($description) }}">
                        <td><strong>{{ $plant }}</strong></td>
                        <td>{{ $unit }}</td>
                        <td><a href="#" class="procurement-service-notification">{{ $notification }}</a></td>
                        <td><strong>{{ $description }}</strong></td>
                        <td><strong>{{ $total }}</strong></td>
                        <td>{{ $serviceMode['process'] }}</td>
                        <td>{{ $procurementStatus }}</td>
                        <td><div class="procurement-service-progress"><i data-progress="{{ $progress }}"></i><strong>{{ $progress }}%</strong></div></td>
                        <td>{{ $resolvedPo }}</td>
                        <td>{{ $deadline }}</td>
                        <td><span class="procurement-goods-status is-{{ $statusTone }}">{{ $requestStatus }}</span></td>
                        <td>
                            <div class="procurement-service-actions">
                                <button type="button" class="is-view" title="Lihat detail" aria-label="Lihat detail jasa {{ $index + 1 }}"><i class="bi bi-search" aria-hidden="true"></i></button>
                                <button type="button" class="is-edit" title="Edit" aria-label="Edit jasa {{ $index + 1 }}"><i class="bi bi-pencil-fill" aria-hidden="true"></i></button>
                            </div>
                        </td>
                        <td>
                            <div class="procurement-service-actions">
                                <button type="button" class="is-document" title="Dokumen" aria-label="Dokumen jasa {{ $index + 1 }}"><i class="bi bi-file-earmark-text-fill" aria-hidden="true"></i></button>
                                <button type="button" class="is-note" title="Catatan" aria-label="Catatan jasa {{ $index + 1 }}"><i class="bi bi-chat-left-text" aria-hidden="true"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="procurement-goods-empty" data-procurement-empty hidden><i class="bi bi-search" aria-hidden="true"></i><span>Data pengadaan jasa tidak ditemukan.</span></div>
    <footer class="procurement-goods-table-footer">
        <span data-procurement-summary aria-live="polite"></span>
        <nav data-procurement-pagination aria-label="Pagination daftar pengadaan jasa"></nav>
    </footer>
</section>
