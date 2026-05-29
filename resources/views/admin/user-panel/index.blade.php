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
                        <tr>
                            <td>
                                <div class="userpanel-user">
                                    <div class="userpanel-avatar">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="userpanel-name">{{ $user->name }}</div>
                                        <div class="text-muted small">ID {{ $user->id }}</div>
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
                                        title="Edit user"
                                        aria-label="Edit user">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
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
        'defaultPassword' => $defaultPassword,
    ])

    @include('admin.user-panel.partials.form-modal', [
        'modalId' => 'editUserModal',
        'title' => 'Edit Akun',
        'action' => '#',
        'method' => 'PUT',
        'usertypeOptions' => $usertypeOptions,
        'roleOptions' => $roleOptions,
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
        .userpanel-avatar { width: 42px; height: 42px; display: grid; place-items: center; border-radius: .65rem; background: #1d4ed8; color: #fff; font-weight: 800; }
        .userpanel-name { color: #172033; font-weight: 800; line-height: 1.2; }
        .userpanel-table .badge { padding: .38rem .58rem; border-radius: .45rem; font-weight: 750; }
        .userpanel-icon-btn { width: 2.05rem; height: 2.05rem; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
        .userpanel-type-toggle { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        .userpanel-type-toggle .btn { min-height: 42px; font-weight: 800; }
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

                if (type === 'admin') {
                    role.value = 'admin';
                } else if (role.value === 'admin') {
                    role.value = 'qc';
                }
            };

            document.querySelectorAll('[data-userpanel-form]').forEach((form) => {
                form.querySelectorAll('input[name="usertype"]').forEach((input) => {
                    input.addEventListener('change', () => syncRoleByType(form));
                });
                form.querySelector('[name="role"]')?.addEventListener('change', () => syncRoleByType(form));
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

                const typeInput = form.querySelector(`input[name="usertype"][value="${button.dataset.usertype}"]`);
                if (typeInput) typeInput.checked = true;

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
        })();
    </script>
@endpush
