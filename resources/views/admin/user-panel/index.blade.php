@extends('layouts.dashboard')

@section('title', 'Userpanel')
@section('page_title', 'Userpanel')

@section('content')
    @php
        $typeBadge = [
            'admin' => 'text-bg-dark',
            'user' => 'text-bg-primary',
        ];
        $roleBadge = [
            'super_admin' => 'text-bg-danger',
            'admin' => 'text-bg-dark',
            'qc' => 'text-bg-info',
            'commissioning' => 'text-bg-success',
            'pgo' => 'text-bg-warning',
            'approval' => 'text-bg-secondary',
        ];
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Data belum bisa disimpan.</strong>
            <div>{{ $errors->first() }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Userpanel</h1>
            <p>Kelola akun admin dan user operasional berdasarkan role akses masing-masing.</p>
        </div>
        <div class="page-actions">
            <form method="POST"
                  action="{{ route('admin.user-panel.registration-access') }}"
                  data-registration-toggle-form
                  data-next-state="{{ $publicRegistrationEnabled ? 'nonaktifkan' : 'aktifkan' }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="enabled" value="{{ $publicRegistrationEnabled ? 0 : 1 }}">
                <button type="submit" class="btn {{ $publicRegistrationEnabled ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    <i class="bi {{ $publicRegistrationEnabled ? 'bi-toggle-on' : 'bi-toggle-off' }} me-1"></i>
                    Register {{ $publicRegistrationEnabled ? 'Aktif' : 'Nonaktif' }}
                </button>
            </form>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus me-1"></i>Tambah Akun
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card userpanel-stat">
                <div class="stat-icon text-bg-primary"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-title">Total User</div>
                    <div class="stat-value">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card userpanel-stat">
                <div class="stat-icon text-bg-dark"><i class="bi bi-shield-lock"></i></div>
                <div>
                    <div class="stat-title">Admin</div>
                    <div class="stat-value">{{ $summary['admin'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card userpanel-stat">
                <div class="stat-icon text-bg-success"><i class="bi bi-person-workspace"></i></div>
                <div>
                    <div class="stat-title">User Role</div>
                    <div class="stat-value">{{ $summary['user'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card userpanel-stat">
                <div class="stat-icon text-bg-secondary"><i class="bi bi-check2-square"></i></div>
                <div>
                    <div class="stat-title">Approval</div>
                    <div class="stat-value">{{ $summary['approval'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card userpanel-filter">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label">Tipe Akun</label>
                <select name="usertype" class="form-select">
                    <option value="all">Semua Tipe</option>
                    @foreach ($usertypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['usertype'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="all">Semua Role</option>
                    @foreach ($roleOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-xl-4">
                <label class="form-label">Cari</label>
                <input type="search" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Nama, email, phone, role">
            </div>
            <div class="col-12 col-xl-2 d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.user-panel') }}" class="btn btn-outline-secondary">Reset</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Daftar Akun</h2>
                <div class="text-muted small">Password hanya dibuat otomatis saat akun baru ditambahkan.</div>
            </div>
            <span class="badge text-bg-light userpanel-default-password">Default: {{ $defaultPassword }}</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle userpanel-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Kontak</th>
                        <th>Tipe</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $areaSummary = filled($user->profile_areas ?? [])
                                ? implode(', ', array_slice($user->profile_areas, 0, 3)).(count($user->profile_areas) > 3 ? ' +' . (count($user->profile_areas) - 3) : '')
                                : 'Semua area';
                            $profilePhotoUrl = $user->profilePhotoUrl();
                        @endphp
                        <tr>
                            <td>
                                <div class="userpanel-user">
                                    <div class="userpanel-avatar">
                                        @if ($profilePhotoUrl)
                                            <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}">
                                        @else
                                            {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="userpanel-name">{{ $user->name }}</div>
                                        <div class="text-muted small userpanel-area-summary">{{ $areaSummary }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $user->email }}</div>
                                <div class="text-muted small">{{ $user->phone ?: 'Tanpa nomor phone' }}</div>
                            </td>
                            <td><span class="badge {{ $typeBadge[$user->usertype] ?? 'text-bg-secondary' }}">{{ $usertypeOptions[$user->usertype] ?? $user->usertype }}</span></td>
                            <td><span class="badge {{ $roleBadge[$user->role] ?? 'text-bg-secondary' }}">{{ $roleOptions[$user->role] ?? Str::headline($user->role) }}</span></td>
                            <td>
                                <div>{{ $user->created_at?->format('d M Y') ?: '-' }}</div>
                                <div class="text-muted small">{{ $user->updated_at?->format('H:i') ?: '-' }}</div>
                            </td>
                            <td class="text-end">
                                <div class="userpanel-actions">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary userpanel-icon-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-update-url="{{ route('admin.user-panel.update', $user) }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-phone="{{ $user->phone }}"
                                            data-usertype="{{ $user->usertype }}"
                                            data-role="{{ $user->role }}"
                                            data-profile-areas='@json($user->profile_areas ?? [])'
                                            data-profile-photo-url="{{ $profilePhotoUrl }}"
                                            title="Edit user"
                                            aria-label="Edit user">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @unless (auth()->id() === $user->id)
                                        <form method="POST"
                                              action="{{ route('admin.user-panel.destroy', $user) }}"
                                              data-delete-user-form
                                              data-delete-label="{{ $user->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger userpanel-icon-btn"
                                                    title="Hapus user"
                                                    aria-label="Hapus user">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada user sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>

    @include('admin.user-panel.partials.form-modal', [
        'modalId' => 'createUserModal',
        'title' => 'Tambah Akun',
        'action' => route('admin.user-panel.store'),
        'method' => null,
        'usertypeOptions' => $usertypeOptions,
        'roleOptions' => $roleOptions,
        'workAreaOptions' => $workAreaOptions,
        'defaultPassword' => $defaultPassword,
    ])

    @include('admin.user-panel.partials.form-modal', [
        'modalId' => 'editUserModal',
        'title' => 'Edit Akun',
        'action' => '#',
        'method' => 'PUT',
        'usertypeOptions' => $usertypeOptions,
        'roleOptions' => $roleOptions,
        'workAreaOptions' => $workAreaOptions,
        'defaultPassword' => null,
    ])
@endsection

@push('styles')
    <style>
        .userpanel-stat { min-height: 92px; }
        .userpanel-filter { background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); }
        .userpanel-default-password { border: 1px solid #dbe3ef; color: #475569; font-weight: 800; }
        .userpanel-table { min-width: 860px; margin-bottom: 0; }
        .userpanel-table thead th { padding: .75rem .8rem; color: #475569; font-size: .76rem; text-transform: uppercase; background: #f8fafc; white-space: nowrap; }
        .userpanel-table tbody td { padding: .85rem .8rem; border-bottom-color: #e8edf4; vertical-align: middle; }
        .userpanel-user { display: flex; align-items: center; gap: .75rem; }
        .userpanel-avatar { width: 42px; height: 42px; display: grid; place-items: center; flex: 0 0 auto; overflow: hidden; border-radius: .65rem; background: #1d4ed8; color: #fff; font-weight: 800; }
        .userpanel-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .userpanel-name { color: #172033; font-weight: 800; line-height: 1.2; }
        .userpanel-area-summary { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .userpanel-photo-editor { display: flex; align-items: center; gap: .85rem; }
        .userpanel-photo-preview { width: 58px; height: 58px; display: grid; place-items: center; flex: 0 0 auto; overflow: hidden; border-radius: .8rem; background: #1d4ed8; color: #fff; font-size: 1.25rem; font-weight: 800; }
        .userpanel-photo-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .userpanel-table .badge { padding: .38rem .58rem; border-radius: .45rem; font-weight: 750; }
        .userpanel-icon-btn { width: 2.05rem; height: 2.05rem; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
        .userpanel-actions { display: inline-flex; justify-content: flex-end; gap: .4rem; }
        .userpanel-actions form { display: inline-flex; }
        .userpanel-type-toggle { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        .userpanel-type-toggle .btn { min-height: 42px; font-weight: 800; }
        .area-picker { display: grid; gap: .55rem; }
        .area-picker-tags { display: flex; flex-wrap: wrap; gap: .45rem; min-height: 2.45rem; padding: .45rem; border: 1px solid #d7dee8; border-radius: .75rem; background: #f8fafc; }
        .area-picker-tags:empty::before { content: "Semua area"; color: #64748b; font-size: .88rem; }
        .area-picker-tag { display: inline-flex; align-items: center; gap: .35rem; max-width: 100%; padding: .34rem .45rem .34rem .65rem; border-radius: 999px; background: #e8f1fb; color: #16324f; font-size: .84rem; font-weight: 800; }
        .area-picker-remove { width: 1.25rem; height: 1.25rem; display: inline-grid; place-items: center; border: 0; border-radius: 999px; background: #d7e6f6; color: #16324f; line-height: 1; }
        .page-actions form { display: inline-flex; }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const syncRoleByType = (form) => {
                const type = form.querySelector('input[name="usertype"]:checked')?.value;
                const role = form.querySelector('[name="role"]');
                if (!role) return;

                const fallback = type === 'admin' ? 'admin' : 'qc';
                let currentOptionIsAvailable = false;

                role.querySelectorAll('option').forEach((option) => {
                    const accountTypes = (option.dataset.accountTypes || '').split(',');
                    const available = accountTypes.includes(type);

                    option.hidden = !available;
                    option.disabled = !available;

                    if (available && option.value === role.value) {
                        currentOptionIsAvailable = true;
                    }
                });

                if (!currentOptionIsAvailable) {
                    role.value = fallback;
                }

                syncAreaField(form);
            };

            const syncAreaField = (form) => {
                const role = form.querySelector('[name="role"]')?.value;
                const group = form.querySelector('[data-userpanel-area-group]');
                const select = form.querySelector('[data-userpanel-area-select]');
                if (!group || !select) return;

                const active = ['qc', 'commissioning'].includes(role);
                group.classList.toggle('d-none', !active);
                select.disabled = !active;

                select.querySelectorAll('option').forEach((option) => {
                    if (!option.value) return;
                    const optionActive = !active || option.dataset.role === role;
                    option.hidden = !optionActive;
                    option.disabled = !optionActive;
                    if (!optionActive) {
                        removeAreaValue(form, option.value);
                    }
                });
            };

            const selectedAreaValues = (form) => Array.from(form.querySelectorAll('[data-area-picker-inputs] input[name="profile_areas[]"]'))
                .map((input) => input.value)
                .filter(Boolean);

            const renderAreaTags = (form) => {
                const tags = form.querySelector('[data-area-picker-tags]');
                if (!tags) return;

                tags.innerHTML = '';
                selectedAreaValues(form).forEach((area) => {
                    const tag = document.createElement('span');
                    tag.className = 'area-picker-tag';
                    tag.textContent = area;

                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'area-picker-remove';
                    remove.setAttribute('aria-label', `Hapus ${area}`);
                    remove.textContent = 'x';
                    remove.addEventListener('click', () => removeAreaValue(form, area));

                    tag.appendChild(remove);
                    tags.appendChild(tag);
                });
            };

            const addAreaValue = (form, area) => {
                if (!area || selectedAreaValues(form).includes(area)) return;

                const inputs = form.querySelector('[data-area-picker-inputs]');
                if (!inputs) return;

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'profile_areas[]';
                input.value = area;
                inputs.appendChild(input);
                renderAreaTags(form);
            };

            const removeAreaValue = (form, area) => {
                form.querySelectorAll('[data-area-picker-inputs] input[name="profile_areas[]"]').forEach((input) => {
                    if (input.value === area) {
                        input.remove();
                    }
                });
                renderAreaTags(form);
            };

            const resetAreaValues = (form, areas = []) => {
                const inputs = form.querySelector('[data-area-picker-inputs]');
                if (!inputs) return;

                inputs.innerHTML = '';
                areas.forEach((area) => addAreaValue(form, area));
                renderAreaTags(form);
            };

            const setupAreaPicker = (form) => {
                const select = form.querySelector('[data-area-picker-select]');
                if (!select) return;

                select.addEventListener('change', () => {
                    addAreaValue(form, select.value);
                    select.value = '';
                });
                renderAreaTags(form);
            };

            const setPhotoPreview = (form, url = '', fallback = 'U') => {
                const preview = form.querySelector('[data-userpanel-photo-preview]');
                if (!preview) return;

                preview.innerHTML = '';
                if (url) {
                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = fallback;
                    preview.appendChild(image);
                    return;
                }

                preview.textContent = (fallback || 'U').trim().charAt(0).toUpperCase() || 'U';
            };

            const setupPhotoInput = (form) => {
                const input = form.querySelector('[data-userpanel-photo-input]');
                if (!input) return;

                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    const fallback = form.querySelector('[name="name"]')?.value || 'U';
                    setPhotoPreview(form, file ? URL.createObjectURL(file) : '', fallback);
                });
            };

            document.querySelectorAll('[data-userpanel-form]').forEach((form) => {
                form.querySelectorAll('input[name="usertype"]').forEach((input) => {
                    input.addEventListener('change', () => syncRoleByType(form));
                });
                form.querySelector('[name="role"]')?.addEventListener('change', () => syncRoleByType(form));
                setupAreaPicker(form);
                setupPhotoInput(form);
                setPhotoPreview(form, '', form.querySelector('[name="name"]')?.value || 'U');
                syncRoleByType(form);
            });

            const editModal = document.getElementById('editUserModal');
            if (!editModal) return;

            editModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (!button) return;

                const form = editModal.querySelector('form');
                form.action = button.dataset.updateUrl;

                ['name', 'email', 'phone', 'role'].forEach((name) => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) input.value = button.dataset[name] || '';
                });

                const photoInput = form.querySelector('[data-userpanel-photo-input]');
                if (photoInput) {
                    photoInput.value = '';
                }

                const typeInput = form.querySelector(`input[name="usertype"][value="${button.dataset.usertype}"]`);
                if (typeInput) typeInput.checked = true;

                resetAreaValues(form, JSON.parse(button.dataset.profileAreas || '[]'));
                setPhotoPreview(form, button.dataset.profilePhotoUrl || '', button.dataset.name || 'U');

                syncRoleByType(form);
            });

            document.querySelectorAll('[data-registration-toggle-form]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    if (!window.Swal) {
                        return;
                    }

                    event.preventDefault();
                    const action = form.dataset.nextState || 'ubah';
                    const result = await Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Akses registrasi publik akan di${action}.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: `Ya, ${action}`,
                        cancelButtonText: 'Batal',
                        confirmButtonColor: action === 'aktifkan' ? '#198754' : '#dc3545',
                    });

                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            document.querySelectorAll('[data-delete-user-form]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    if (!window.Swal) {
                        return;
                    }

                    event.preventDefault();
                    const label = form.dataset.deleteLabel || 'akun ini';
                    const result = await Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Akun ${label} akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545',
                    });

                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        })();
    </script>
@endpush
