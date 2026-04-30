<div class="user-simple-form-header">
    <h1>{{ $pageTitle }}</h1>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <x-user.action-card :title="$formTitle" description="" icon="bi-gear-wide-connected" class="mb-0">
            <x-user.commissioning-record-sheet :record="$commissioningRecord" />
        </x-user.action-card>
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
