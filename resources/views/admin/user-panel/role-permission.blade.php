@extends('layouts.dashboard')

@section('title', 'Role & Permission')
@section('page_title', 'Role & Permission')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="page-header">
        <div>
            <h1>Role & Permission</h1>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach ($adminRoles as $key => $label)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="content-card role-permission-card {{ $key === 'super_admin' ? 'is-super' : '' }}">
                    <div class="role-permission-icon">
                        <i class="bi {{ $key === 'super_admin' ? 'bi-shield-fill-check' : ($key === 'approval' ? 'bi-check2-square' : 'bi-person-gear') }}"></i>
                    </div>
                    <div>
                        <h2>{{ $label }}</h2>
                        <p>
                            @if ($key === 'super_admin')
                                Akses penuh ke seluruh menu dan pengaturan permission.
                            @elseif ($key === 'admin')
                                Akses admin operasional sesuai menu yang diaktifkan.
                            @else
                                Akses panel admin untuk monitoring/approval sesuai kebutuhan.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <form id="rolePermissionForm" method="POST" action="{{ route('admin.user-panel.role-permission.update') }}">
        @csrf
        @method('PATCH')

        <div class="content-card">
            <div class="card-heading">
                <div>
                    <h2>Menu Access Matrix</h2>
                    <div class="text-muted small">Centang menu yang boleh tampil di sidebar untuk setiap role.</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle role-permission-table">
                    <thead>
                        <tr>
                            <th>Group Menu</th>
                            <th>Sub Menu</th>
                            <th class="text-center">Super Admin</th>
                            @foreach ($configurableRoles as $roleLabel)
                                <th class="text-center">{{ $roleLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($menuOptions as $group)
                            @foreach ($group['children'] as $menuKey => $menuLabel)
                                <tr>
                                    @if ($loop->first)
                                        <td rowspan="{{ count($group['children']) }}" class="fw-bold">{{ $group['label'] }}</td>
                                    @endif
                                    <td>{{ $menuLabel }}</td>
                                    <td class="text-center">
                                        <span class="role-permission-check is-enabled" title="Super Admin selalu aktif">
                                            <i class="bi bi-check-lg"></i>
                                        </span>
                                    </td>
                                    @foreach ($configurableRoles as $roleKey => $roleLabel)
                                        @php($checked = in_array($menuKey, $permissions[$roleKey] ?? [], true))
                                        <td class="text-center">
                                            <label class="role-permission-switch" title="{{ $roleLabel }} - {{ $menuLabel }}">
                                                <input type="checkbox" name="permissions[{{ $roleKey }}][]" value="{{ $menuKey }}" @checked($checked)>
                                                <span></span>
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="role-permission-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Simpan Permission
                </button>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .role-permission-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            min-height: 118px;
            margin-bottom: 0;
        }
        .role-permission-card.is-super {
            border-color: #fecaca;
            background: linear-gradient(180deg, #fff 0%, #fff7f7 100%);
        }
        .role-permission-card h2 {
            margin: 0;
            color: #172033;
            font-size: 1rem;
            font-weight: 800;
        }
        .role-permission-card p {
            margin: .2rem 0 0;
            color: #64748b;
            font-size: .86rem;
        }
        .role-permission-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: .65rem;
            background: #1d4ed8;
            color: #fff;
            font-size: 1.2rem;
        }
        .role-permission-card.is-super .role-permission-icon {
            background: #dc2626;
        }
        .role-permission-table {
            min-width: 920px;
            margin-bottom: 0;
        }
        .role-permission-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .role-permission-table thead th {
            color: #475569;
            font-size: .76rem;
            text-transform: uppercase;
            background: #f8fafc;
            white-space: nowrap;
        }
        .role-permission-check {
            width: 1.75rem;
            height: 1.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-weight: 800;
        }
        .role-permission-check.is-enabled {
            background: #dcfce7;
            color: #166534;
        }
        .role-permission-switch {
            display: inline-flex;
            cursor: pointer;
        }
        .role-permission-switch input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .role-permission-switch span {
            width: 2.65rem;
            height: 1.45rem;
            position: relative;
            display: inline-block;
            border-radius: 999px;
            background: #e2e8f0;
            transition: background .18s ease;
        }
        .role-permission-switch span::after {
            content: "";
            position: absolute;
            top: .18rem;
            left: .2rem;
            width: 1.08rem;
            height: 1.08rem;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, .22);
            transition: transform .18s ease;
        }
        .role-permission-switch input:checked + span {
            background: #16a34a;
        }
        .role-permission-switch input:checked + span::after {
            transform: translateX(1.16rem);
        }
    </style>
@endpush
