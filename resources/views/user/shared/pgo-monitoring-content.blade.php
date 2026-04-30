<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="Monitoring PGO" />

<div class="row g-4">
    @foreach ($cards as $card)
        <div class="col-md-4">
            <article class="inspector-mobile-card h-100">
                <span class="inspector-chip">{{ $card['title'] }}</span>
                <h2 class="mt-3 mb-2">{{ $card['value'] }}</h2>
                <p class="text-muted mb-0">{{ $card['description'] }}</p>
            </article>
        </div>
    @endforeach

    <div class="col-12">
        <x-user.action-card title="Progress Pekerjaan" description="Table monitoring progres pekerjaan dummy." icon="bi-bar-chart-steps">
            <div class="table-responsive">
                <table class="table align-middle inspector-table">
                    <thead><tr><th>Pekerjaan</th><th>Plant</th><th>Area</th><th>Progress</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['job'] }}</td>
                                <td>{{ $row['plant'] }}</td>
                                <td>{{ $row['area'] }}</td>
                                <td>{{ $row['progress'] }}</td>
                                <td><x-user.status-badge :status="$row['status']" /></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary">Detail</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-user.action-card>
    </div>
</div>
