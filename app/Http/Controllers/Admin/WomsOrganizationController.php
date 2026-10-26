<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSectionMapping;
use App\Models\WomsDepartment;
use App\Models\WomsOrganizationSyncRun;
use App\Models\WomsPersonnel;
use App\Models\WomsSection;
use App\Models\WomsUnit;
use App\Services\Integrations\WomsOrganizationSyncService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WomsOrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'department_id' => $request->integer('department_id') ?: null,
            'unit_id' => $request->integer('unit_id') ?: null,
            'section_id' => $request->integer('section_id') ?: null,
            'personnel' => trim((string) $request->input('personnel')),
            'presence' => in_array($request->input('presence'), ['present', 'missing'], true)
                ? $request->input('presence')
                : null,
        ];

        $departments = WomsDepartment::query()
            ->with([
                'generalManager',
                'units' => fn ($query) => $query
                    ->with(['seniorManager', 'sections' => fn ($query) => $query->with('manager')->orderBy('name')])
                    ->orderBy('name'),
            ])
            ->when($filters['department_id'], fn (Builder $query, int $id) => $query->whereKey($id))
            ->when($filters['unit_id'], fn (Builder $query, int $id) => $query->whereHas('units', fn (Builder $unitQuery) => $unitQuery->whereKey($id)))
            ->when($filters['section_id'], fn (Builder $query, int $id) => $query->whereHas('units.sections', fn (Builder $sectionQuery) => $sectionQuery->whereKey($id)))
            ->when($filters['personnel'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['personnel'];
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('generalManager', fn (Builder $personQuery) => $this->personnelSearch($personQuery, $search))
                        ->orWhereHas('units.seniorManager', fn (Builder $personQuery) => $this->personnelSearch($personQuery, $search))
                        ->orWhereHas('units.sections.manager', fn (Builder $personQuery) => $this->personnelSearch($personQuery, $search));
                });
            })
            ->when($filters['presence'], function (Builder $query, string $presence): void {
                $isPresent = $presence === 'present';
                $query->where(function (Builder $query) use ($isPresent): void {
                    $query->where('present_in_source', $isPresent)
                        ->orWhereHas('units', fn (Builder $unitQuery) => $unitQuery->where('present_in_source', $isPresent))
                        ->orWhereHas('units.sections', fn (Builder $sectionQuery) => $sectionQuery->where('present_in_source', $isPresent));
                });
            })
            ->orderBy('name')
            ->paginate(5)
            ->withQueryString();

        $latestRun = WomsOrganizationSyncRun::query()->latest('updated_at')->first();
        $lastSuccessfulRun = WomsOrganizationSyncRun::query()
            ->whereIn('status', WomsOrganizationSyncRun::successfulStatuses())
            ->latest('finished_at')
            ->first();

        return view('admin.woms-organization.index', [
            'departments' => $departments,
            'departmentOptions' => WomsDepartment::query()->orderBy('name')->get(['id', 'name']),
            'unitOptions' => WomsUnit::query()->with('department')->orderBy('name')->get(['id', 'woms_department_id', 'name']),
            'sectionOptions' => WomsSection::query()->with('unit')->orderBy('name')->get(['id', 'woms_unit_id', 'name']),
            'filters' => $filters,
            'summary' => [
                'departments' => WomsDepartment::query()->where('present_in_source', true)->count(),
                'units' => WomsUnit::query()->where('present_in_source', true)->count(),
                'sections' => WomsSection::query()->where('present_in_source', true)->count(),
                'personnel' => WomsPersonnel::query()->where('present_in_source', true)->count(),
                'missing' => WomsDepartment::query()->where('present_in_source', false)->count()
                    + WomsUnit::query()->where('present_in_source', false)->count()
                    + WomsSection::query()->where('present_in_source', false)->count()
                    + WomsPersonnel::query()->where('present_in_source', false)->count(),
                'verified_mappings' => OrganizationSectionMapping::query()
                    ->where('mapping_status', OrganizationSectionMapping::STATUS_VERIFIED)
                    ->count(),
            ],
            'latestRun' => $latestRun,
            'lastSuccessfulRun' => $lastSuccessfulRun,
            'recentRuns' => WomsOrganizationSyncRun::query()
                ->with('triggeredBy:id,name')
                ->latest('started_at')
                ->limit(10)
                ->get(),
            'connectionStatus' => $this->connectionStatus($latestRun),
            'canSync' => (bool) $request->user()?->isSuperAdmin(),
        ]);
    }

    public function chart(): JsonResponse
    {
        $departments = WomsDepartment::query()
            ->present()
            ->with([
                'generalManager',
                'units' => fn ($query) => $query
                    ->present()
                    ->with([
                        'seniorManager',
                        'sections' => fn ($query) => $query
                            ->present()
                            ->with('manager')
                            ->orderBy('name'),
                    ])
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'departments' => $departments->map(fn (WomsDepartment $department): array => [
                'name' => $department->name,
                'general_manager' => $department->generalManager?->name,
                'units' => $department->units->map(fn (WomsUnit $unit): array => [
                    'name' => $unit->name,
                    'senior_manager' => $unit->seniorManager?->name,
                    'sections' => $unit->sections->map(fn (WomsSection $section): array => [
                        'name' => $section->name,
                        'manager' => $section->manager?->name,
                    ])->values(),
                ])->values(),
            ])->values(),
            'summary' => [
                'departments' => $departments->count(),
                'units' => $departments->sum(fn (WomsDepartment $department): int => $department->units->count()),
                'sections' => $departments->sum(fn (WomsDepartment $department): int => $department->units->sum(
                    fn (WomsUnit $unit): int => $unit->sections->count()
                )),
            ],
        ]);
    }

    public function sync(Request $request, WomsOrganizationSyncService $syncService): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'confirm_large_reduction' => ['nullable', 'boolean'],
            'expected_checksum' => ['nullable', 'required_if:confirm_large_reduction,1', 'regex:/\A[a-f0-9]{64}\z/i'],
        ]);
        $run = $syncService->sync(
            WomsOrganizationSyncRun::TRIGGER_ADMIN,
            $request->user(),
            (bool) ($validated['confirm_large_reduction'] ?? false),
            $validated['expected_checksum'] ?? null
        );

        $flashType = match ($run->status) {
            WomsOrganizationSyncRun::STATUS_SUCCESS,
            WomsOrganizationSyncRun::STATUS_NO_CHANGES => 'success',
            WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW => 'warning',
            default => 'error',
        };
        $message = match ($run->status) {
            WomsOrganizationSyncRun::STATUS_SUCCESS => "Sinkronisasi selesai: {$run->created_count} dibuat, {$run->updated_count} diperbarui, {$run->marked_missing_count} ditandai tidak ada di sumber.",
            WomsOrganizationSyncRun::STATUS_NO_CHANGES => 'Snapshot WOMS tidak berubah dan data lokal konsisten.',
            default => $run->sanitized_error_message ?? 'Sinkronisasi WOMS gagal. Data lokal tidak diubah.',
        };

        return redirect()
            ->route('admin.woms-organization.index')
            ->with($flashType, $message);
    }

    private function personnelSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): void {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * @return array{label: string, tone: string, description: string}
     */
    private function connectionStatus(?WomsOrganizationSyncRun $latestRun): array
    {
        $configured = trim((string) config('services.woms_organization.base_url')) !== ''
            && trim((string) config('services.woms_organization.token')) !== '';

        if (! $configured) {
            return [
                'label' => 'Belum dikonfigurasi',
                'tone' => 'secondary',
                'description' => 'URL atau token integrasi WOMS belum tersedia pada konfigurasi server.',
            ];
        }

        if (! $latestRun) {
            return [
                'label' => 'Belum disinkronkan',
                'tone' => 'warning',
                'description' => 'Konfigurasi tersedia, tetapi belum ada hasil sinkronisasi yang tercatat.',
            ];
        }

        return match ($latestRun->status) {
            WomsOrganizationSyncRun::STATUS_SUCCESS,
            WomsOrganizationSyncRun::STATUS_NO_CHANGES => [
                'label' => 'Terhubung',
                'tone' => 'success',
                'description' => 'Sinkronisasi terakhir berhasil dan master lokal dapat digunakan.',
            ],
            WomsOrganizationSyncRun::STATUS_RUNNING => [
                'label' => 'Sedang sinkronisasi',
                'tone' => 'info',
                'description' => 'Proses sinkronisasi sedang berjalan.',
            ],
            WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW => [
                'label' => 'Perlu review',
                'tone' => 'warning',
                'description' => $latestRun->sanitized_error_message ?? 'Perubahan sumber perlu diperiksa oleh Super Admin.',
            ],
            default => [
                'label' => 'Gagal sinkronisasi',
                'tone' => 'danger',
                'description' => $latestRun->sanitized_error_message ?? 'Sinkronisasi terakhir gagal. Master lokal terakhir dipertahankan.',
            ],
        };
    }
}
