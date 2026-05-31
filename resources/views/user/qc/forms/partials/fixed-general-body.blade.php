<section class="inspector-panel qc-form-card">
    <div class="qc-user-table-wrap qc-mobile-card-wrap qc-general-check-wrap">
        <table class="qc-user-checklist-table qc-mobile-card-table qc-general-check-table">
            <thead>
                <tr>
                    <th>Item Pengecekan</th>
                    <th>Standar</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($generalRows as $index => $row)
                    <tr>
                        <td data-label="Item Pengecekan">
                            <input type="hidden" name="body[general_rows][{{ $index }}][item_pengecekan]" value="{{ $row['item_pengecekan'] ?? '' }}">
                            <div class="qc-readonly-template-text">{{ $row['item_pengecekan'] ?? '' }}</div>
                        </td>
                        <td data-label="Standar">
                            <input type="hidden" name="body[general_rows][{{ $index }}][standar]" value="{{ $row['standar'] ?? '' }}">
                            <div class="qc-readonly-template-text">{{ $row['standar'] ?? '' }}</div>
                        </td>
                        <td data-label="Status">
                            <div class="qc-user-status-inline">
                                <label><input type="radio" name="body[general_rows][{{ $index }}][status]" value="Ok" @checked(($row['status'] ?? null) === 'Ok') data-qc-ok-status> <span>Ok</span></label>
                                <label><input type="radio" name="body[general_rows][{{ $index }}][status]" value="Not Ok" @checked(($row['status'] ?? null) === 'Not Ok') data-qc-not-ok-status> <span>Not Ok</span></label>
                            </div>
                        </td>
                        <td data-label="Catatan"><textarea name="body[general_rows][{{ $index }}][catatan]" class="form-control qc-user-table-note">{{ $row['catatan'] ?? '' }}</textarea></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3" data-label="Info">Belum ada row default.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
