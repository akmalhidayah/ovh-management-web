@if (($roleUi['role'] ?? null) === 'qc')
    <div class="user-simple-form-header">
        <h1>{{ $pageTitle }}</h1>
    </div>
@else
    <x-user.page-header :title="$pageTitle" :subtitle="$pageSubtitle" eyebrow="Form Workspace" />
@endif

<x-user.form-template-selector :catalog="$templateCatalog" :default-category="$defaultCategory" :default-template="$defaultTemplate" :general-info="$generalInfo" :summary="$templateSummary" />

<div class="row g-4 mt-1 d-none" data-form-workspace>
    <div class="col-xl-8">
        <section class="inspector-panel">
            @if (($roleUi['role'] ?? null) === 'qc' && ! empty($qcRecord))
                <x-user.action-card :title="$formTitle" description="" icon="bi-table" class="mb-0" :dynamic-title="true">
                    <x-user.qc-record-sheet :record="$qcRecord" />
                </x-user.action-card>
            @else
                <x-user.action-card title="Checklist Pemeriksaan" description="Isi checklist berdasarkan template terpilih dan tandai item yang perlu follow up." icon="bi-ui-checks-grid" class="mb-4">
                    <x-user.checklist-table :items="$checklist" />
                </x-user.action-card>

                <x-user.action-card :title="$resultTitle" :description="$resultNote" icon="bi-graph-up-arrow" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nilai Aktual</label><input type="text" class="form-control" value="12.4"></div>
                        <div class="col-md-6"><label class="form-label">Satuan</label><input type="text" class="form-control" value="bar"></div>
                        <div class="col-md-6"><label class="form-label">Standar</label><input type="text" class="form-control" value="10 - 14 bar"></div>
                        <div class="col-md-6">
                            <label class="form-label">Status Akhir</label>
                            <select class="form-select">
                                <option>OK</option>
                                <option>Follow Up</option>
                                <option>Not OK</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan Hasil</label>
                            <textarea class="form-control" rows="4">Parameter utama masih dalam rentang standar, namun ada item yang memerlukan verifikasi lanjutan sebelum proses berikutnya.</textarea>
                        </div>
                    </div>
                </x-user.action-card>

                <x-user.action-card :title="$findingTitle" :description="$findingNote" icon="bi-lightbulb">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Temuan Pemeriksaan</label>
                            <textarea class="form-control" rows="4">Ditemukan deformasi ringan pada support frame dan pressure gauge memerlukan kalibrasi ulang.</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Rekomendasi Tindak Lanjut</label>
                            <textarea class="form-control" rows="4">Lakukan pengecekan struktur lanjutan dan kalibrasi ulang instrument sebelum unit dipakai penuh.</textarea>
                        </div>
                    </div>
                </x-user.action-card>
            @endif
        </section>
    </div>

    <div class="col-xl-4">
        <div class="d-grid gap-4 inspector-sticky-column">
            <x-user.upload-card :files="$uploads" />

            <x-user.action-card title="Action Form" description="Simulasi alur simpan draft dan submit." icon="bi-send">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg">Simpan Draft</button>
                    <button type="button" class="btn btn-success btn-lg">Submit Form</button>
                    <a href="{{ route($roleUi['nav'][0]['route']) }}" class="btn btn-outline-secondary btn-lg">Batal</a>
                </div>
            </x-user.action-card>
        </div>
    </div>
</div>
