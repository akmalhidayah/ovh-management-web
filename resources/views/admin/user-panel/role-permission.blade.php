@extends('layouts.dashboard')

@section('title', 'Role & Permission')
@section('page_title', 'Role & Permission')

@section('content')
    <div class="page-header">
        <div>
            <h1>Role & Permission</h1>
            <p>Draft pengaturan akses role. Halaman ini masih placeholder dan belum menyimpan perubahan.</p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-outline-secondary" disabled>
                <i class="bi bi-lock me-1"></i>Dummy Mode
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach ($roles as $key => $label)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="content-card role-permission-card">
                    <div class="role-permission-icon">
                        <i class="bi {{ $key === 'admin' ? 'bi-shield-lock' : 'bi-person-badge' }}"></i>
                    </div>
                    <div>
                        <h2>{{ $label }}</h2>
                        <p>{{ $key === 'admin' ? 'Akses penuh ke panel administrasi.' : 'Akses operasional sesuai fungsi user.' }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h2>Matrix Permission</h2>
                <div class="text-muted small">Contoh tampilan awal untuk desain pengaturan role berikutnya.</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle role-permission-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Permission</th>
                        @foreach ($roles as $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        @foreach ($permission['actions'] as $index => $action)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ count($permission['actions']) }}" class="fw-bold">{{ $permission['module'] }}</td>
                                @endif
                                <td>{{ $action }}</td>
                                @foreach ($roles as $key => $label)
                                    @php
                                        $enabled = $key === 'admin'
                                            || ($key === 'qc' && $permission['module'] === 'QC')
                                            || ($key === 'commissioning' && $permission['module'] === 'Commissioning')
                                            || ($key === 'approval' && $permission['module'] === 'Approval')
                                            || ($key === 'pgo' && $permission['module'] === 'Dashboard');
                                    @endphp
                                    <td class="text-center">
                                        <span class="role-permission-check {{ $enabled ? 'is-enabled' : 'is-disabled' }}">
                                            <i class="bi {{ $enabled ? 'bi-check-lg' : 'bi-dash-lg' }}"></i>
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
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
        .role-permission-table {
            min-width: 980px;
            margin-bottom: 0;
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
        .role-permission-check.is-disabled {
            background: #f1f5f9;
            color: #94a3b8;
        }
    </style>
@endpush
