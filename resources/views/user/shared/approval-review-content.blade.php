<x-user.page-header :title="$title" :subtitle="$subtitle" eyebrow="Review Form" />

<div class="row g-4">
    <div class="col-xl-4">
        <x-user.action-card title="Informasi Form" description="Ringkasan metadata form yang sedang direview." icon="bi-file-earmark-text">
            <dl class="inspector-detail-list">
                @foreach ($form_info as $label => $value)
                    <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                @endforeach
            </dl>
        </x-user.action-card>
    </div>
    <div class="col-xl-4">
        <x-user.action-card title="Informasi Pengirim" description="Data dummy pengirim form." icon="bi-person-badge">
            <dl class="inspector-detail-list">
                @foreach ($sender_info as $label => $value)
                    <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                @endforeach
            </dl>
        </x-user.action-card>
    </div>
    <div class="col-xl-4">
        <x-user.upload-card :files="$documents" />
    </div>

    <div class="col-xl-8">
        <x-user.action-card title="Checklist / Hasil Pemeriksaan" description="Contoh hasil form yang sedang direview." icon="bi-ui-checks-grid">
            <x-user.checklist-table :items="$checklist" />
        </x-user.action-card>
    </div>

    <div class="col-xl-4">
        <x-user.action-card title="Catatan Approval" description="Dummy area review untuk approval officer." icon="bi-chat-square-text">
            <div class="mb-3">
                <label class="form-label">Catatan Review</label>
                <textarea class="form-control" rows="6">Secara umum form sudah lengkap, namun ada item instrumentasi yang perlu follow up. Pastikan bukti kalibrasi dilampirkan.</textarea>
            </div>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-success btn-lg">Approve</button>
                <button type="button" class="btn btn-warning btn-lg text-white">Minta Revisi</button>
                <button type="button" class="btn btn-outline-danger btn-lg">Reject</button>
            </div>
        </x-user.action-card>
    </div>
</div>
