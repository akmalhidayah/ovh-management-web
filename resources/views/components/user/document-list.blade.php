@props(['rows' => []])

<div>
    <div class="table-responsive d-none d-lg-block">
        <table class="table align-middle inspector-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokumen</th>
                    <th>Terkait Form</th>
                    <th>Equipment</th>
                    <th>Kategori</th>
                    <th>Tanggal Upload</th>
                    <th>Ukuran</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['no'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['form'] }}</td>
                        <td>{{ $row['equipment'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['uploaded_at'] }}</td>
                        <td>{{ $row['size'] }}</td>
                        <td><x-user.status-badge :status="$row['status']" /></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ asset($row['file']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Preview</a>
                                <a href="{{ asset($row['file']) }}" target="_blank" class="btn btn-sm btn-primary">Download</a>
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
                        <span class="inspector-chip">{{ $row['form'] }}</span>
                        <h3 class="mt-2">{{ $row['name'] }}</h3>
                    </div>
                    <x-user.status-badge :status="$row['status']" />
                </div>
                <p class="mb-1"><strong>Equipment:</strong> {{ $row['equipment'] }}</p>
                <p class="mb-1"><strong>Kategori:</strong> {{ $row['category'] }}</p>
                <p class="mb-3"><strong>Ukuran:</strong> {{ $row['size'] }}</p>
                <small class="text-muted d-block mb-3">Upload {{ $row['uploaded_at'] }}</small>
                <div class="d-grid gap-2">
                    <a href="{{ asset($row['file']) }}" target="_blank" class="btn btn-outline-secondary">Preview PDF</a>
                    <a href="{{ asset($row['file']) }}" target="_blank" class="btn btn-primary">Download</a>
                </div>
            </article>
        @endforeach
    </div>
</div>
