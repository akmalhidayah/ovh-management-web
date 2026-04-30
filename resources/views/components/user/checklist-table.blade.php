@props(['items' => []])

<div class="inspector-checklist-table">
    <div class="table-responsive d-none d-lg-block">
        <table class="table align-middle inspector-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Parameter</th>
                    <th>Standar</th>
                    <th>Hasil</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['no'] }}</td>
                        <td>{{ $item['parameter'] }}</td>
                        <td>{{ $item['standard'] }}</td>
                        <td>
                            <select class="form-select">
                                @foreach (['Sesuai', 'Tidak Sesuai', 'N/A', 'Perlu Verifikasi'] as $option)
                                    <option value="{{ $option }}" @selected($option === $item['result'])>{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><x-user.status-badge :status="$item['status']" /></td>
                        <td class="text-muted">{{ $item['notes'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-grid gap-3 d-lg-none">
        @foreach ($items as $item)
            <article class="inspector-mobile-card">
                <div class="d-flex justify-content-between gap-3 mb-3">
                    <div>
                        <span class="inspector-chip">Item {{ $item['no'] }}</span>
                        <h3 class="mt-2">{{ $item['parameter'] }}</h3>
                    </div>
                    <x-user.status-badge :status="$item['status']" />
                </div>
                <p class="mb-2"><strong>Standar:</strong> {{ $item['standard'] }}</p>
                <label class="form-label small">Hasil</label>
                <select class="form-select mb-3">
                    @foreach (['Sesuai', 'Tidak Sesuai', 'N/A', 'Perlu Verifikasi'] as $option)
                        <option value="{{ $option }}" @selected($option === $item['result'])>{{ $option }}</option>
                    @endforeach
                </select>
                <p class="text-muted mb-0">{{ $item['notes'] }}</p>
            </article>
        @endforeach
    </div>
</div>
