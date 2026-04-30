@props([
    'rows' => [],
    'type' => 'history',
    'pdfAsset' => null,
])

<div>
    <div class="table-responsive d-none d-lg-block">
        <table class="table align-middle inspector-table">
            <thead>
                <tr>
                    @if ($type === 'history')
                        <th>No</th>
                        <th>Tanggal Submit</th>
                        <th>No Form</th>
                        <th>Kategori</th>
                        <th>Jenis Form</th>
                        <th>Equipment</th>
                        <th>Plant</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    @else
                        <th>No</th>
                        <th>Tanggal Submit</th>
                        <th>No Form</th>
                        <th>Pengirim</th>
                        <th>Kategori</th>
                        <th>Jenis Form</th>
                        <th>Equipment</th>
                        <th>Plant</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['no'] }}</td>
                        <td>{{ $row['submitted_at'] }}</td>
                        <td>{{ $row['form_no'] }}</td>
                        @if ($type === 'pending')
                            <td>{{ $row['sender'] }}</td>
                        @endif
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['form_type'] }}</td>
                        <td>{{ $row['equipment'] }}</td>
                        <td>{{ $row['plant'] }}</td>
                        @if ($type === 'history')
                            <td>{{ $row['area'] }}</td>
                        @endif
                        <td><x-user.status-badge :status="$row['status']" /></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                @if ($type === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Detail</button>
                                    <button type="button" class="btn btn-sm btn-primary">Review</button>
                                @else
                                    <a href="{{ $pdfAsset ? asset($pdfAsset) : '#' }}" target="_blank" class="btn btn-sm btn-primary inspector-icon-action" title="Buka PDF" aria-label="Buka PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-grid gap-3 d-lg-none">
        @foreach ($rows as $row)
            <article class="inspector-mobile-card">
                <div class="d-flex justify-content-between gap-3 mb-3">
                    <div>
                        <span class="inspector-chip">{{ $row['form_no'] }}</span>
                        <h3 class="mt-2">{{ $row['form_type'] }}</h3>
                    </div>
                    <x-user.status-badge :status="$row['status']" />
                </div>
                <p class="mb-1"><strong>Equipment:</strong> {{ $row['equipment'] }}</p>
                <p class="mb-1"><strong>Plant:</strong> {{ $row['plant'] }}</p>
                @if (isset($row['area']))
                    <p class="mb-1"><strong>Area:</strong> {{ $row['area'] }}</p>
                @endif
                @if (isset($row['sender']))
                    <p class="mb-1"><strong>Pengirim:</strong> {{ $row['sender'] }}</p>
                @endif
                <small class="text-muted d-block mb-3">{{ $row['submitted_at'] }}</small>
                <div class="d-grid gap-2">
                    @if ($type === 'pending')
                        <button type="button" class="btn btn-outline-secondary">Detail</button>
                        <button type="button" class="btn btn-primary">Review</button>
                    @else
                        <a href="{{ $pdfAsset ? asset($pdfAsset) : '#' }}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Buka PDF
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</div>
