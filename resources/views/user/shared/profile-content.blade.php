<x-user.page-header :title="'Profil '.$roleUi['brand_title']" subtitle="Kelola informasi akun, nomor HP, dan foto profil." eyebrow="Akun Role User" />

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Periksa kembali input profil.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 align-items-start profile-page-grid">
    <div class="col-xl-7">
        <x-user.action-card title="Edit Profil" description="Perbarui data kontak dan foto profil." icon="bi-person-gear">
            <form method="POST" action="{{ route('user.'.$roleUi['role'].'.profile.update') }}" enctype="multipart/form-data" class="profile-edit-form">
                @csrf
                @method('PATCH')

                <div class="profile-photo-editor">
                    <span class="profile-photo-preview">
                        @if (! empty($profile['photo_url']))
                            <img src="{{ $profile['photo_url'] }}" alt="{{ $profile['name'] }}">
                        @else
                            {{ strtoupper(substr($profile['name'] ?? 'U', 0, 1)) }}
                        @endif
                    </span>
                    <div>
                        <label class="form-label">Foto Profil</label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <small class="text-muted">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $profile['name']) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile['phone'] ?? '') }}" placeholder="Contoh: 0812-xxxx-xxxx">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ $profile['email'] }}" readonly>
                    </div>
                    @if (in_array($roleUi['role'], ['qc', 'commissioning'], true))
                        @php
                            $selectedProfileAreas = old('profile_areas', $profile['areas'] ?? []);
                        @endphp
                        <div class="col-12">
                            <label class="form-label">Area Terkait</label>
                            <div class="area-picker" data-area-picker>
                                <select class="form-select" data-area-picker-select>
                                    <option value="">Pilih area</option>
                                    @foreach ($profile['area_options'] ?? [] as $areaOption)
                                        <option value="{{ $areaOption }}">{{ $areaOption }}</option>
                                    @endforeach
                                </select>
                                <div class="area-picker-tags" data-area-picker-tags></div>
                                <div data-area-picker-inputs>
                                    @foreach ($selectedProfileAreas as $selectedArea)
                                        <input type="hidden" name="profile_areas[]" value="{{ $selectedArea }}">
                                    @endforeach
                                </div>
                            </div>
                            <small class="text-muted">Pilih satu atau beberapa area. Kosongkan pilihan untuk menampilkan semua area.</small>
                        </div>
                    @endif
                    <div class="col-12 d-flex flex-column flex-sm-row gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Profil</button>
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                    </div>
                </div>
            </form>
        </x-user.action-card>
    </div>

    <div class="col-xl-5">
        <x-user.action-card title="Informasi Akun" description="Data akun dan area kerja." icon="bi-person-vcard" class="profile-side-card">
            <dl class="inspector-detail-list profile-list">
                <div><dt>Nama</dt><dd>{{ $profile['name'] }}</dd></div>
                <div><dt>Email</dt><dd>{{ $profile['email'] }}</dd></div>
                <div><dt>Nomor HP</dt><dd>{{ $profile['phone'] ?: '-' }}</dd></div>
                <div><dt>Usertype</dt><dd>{{ $profile['usertype'] }}</dd></div>
                <div><dt>Role</dt><dd>{{ $profile['role'] }}</dd></div>
                <div><dt>Plant Terkait</dt><dd>{{ filled($profile['plants'] ?? []) ? implode(', ', $profile['plants']) : '-' }}</dd></div>
                <div><dt>Area Terkait</dt><dd>{{ filled($profile['areas'] ?? []) ? implode(', ', $profile['areas']) : '-' }}</dd></div>
                <div><dt>Jabatan</dt><dd>{{ $profile['position'] }}</dd></div>
            </dl>
        </x-user.action-card>

        <x-user.action-card title="Statistik Singkat" description="Ringkasan progres pekerjaan." icon="bi-speedometer2" class="profile-side-card mt-3">
            <div class="row g-2">
                @foreach ($stats as $stat)
                    <div class="col-6">
                        <x-user.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :accent="$stat['accent']" />
                    </div>
                @endforeach
            </div>
        </x-user.action-card>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            document.querySelectorAll('[data-area-picker]').forEach((picker) => {
                const select = picker.querySelector('[data-area-picker-select]');
                const tags = picker.querySelector('[data-area-picker-tags]');
                const inputs = picker.querySelector('[data-area-picker-inputs]');

                if (!select || !tags || !inputs) {
                    return;
                }

                const selectedValues = () => Array.from(inputs.querySelectorAll('input[name="profile_areas[]"]'))
                    .map((input) => input.value)
                    .filter(Boolean);

                const render = () => {
                    tags.innerHTML = '';

                    selectedValues().forEach((area) => {
                        const tag = document.createElement('span');
                        tag.className = 'area-picker-tag';
                        tag.textContent = area;

                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'area-picker-remove';
                        remove.setAttribute('aria-label', `Hapus ${area}`);
                        remove.textContent = 'x';
                        remove.addEventListener('click', () => {
                            inputs.querySelectorAll('input[name="profile_areas[]"]').forEach((input) => {
                                if (input.value === area) {
                                    input.remove();
                                }
                            });
                            render();
                        });

                        tag.appendChild(remove);
                        tags.appendChild(tag);
                    });
                };

                select.addEventListener('change', () => {
                    const area = select.value;
                    if (area && !selectedValues().includes(area)) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'profile_areas[]';
                        input.value = area;
                        inputs.appendChild(input);
                        render();
                    }
                    select.value = '';
                });

                render();
            });
        })();
    </script>
@endpush
