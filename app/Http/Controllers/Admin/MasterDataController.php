<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use App\Models\OrganizationSection;
use App\Services\MasterDataInspectionStatusService;
use App\Services\MasterDataStatusService;
use App\Services\MasterDataUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filtersFromRequest($request);
        $filteredQuery = $this->filteredRecordsQuery($filters);
        $filteredRecordCount = (clone $filteredQuery)->count();

        $records = $filteredQuery
            ->with('organizationSection')
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderBy('document_category')
            ->orderBy('plant')
            ->orderBy('area')
            ->orderBy('func_location')
            ->orderBy('equipment_no')
            ->paginate(20)
            ->withQueryString();

        return view('admin.master-data', [
            'records' => $records,
            'filters' => $filters,
            'filterOptions' => [
                'years' => $this->distinctOptions('year'),
                'plants' => $this->distinctOptions('plant'),
                'areas' => $this->distinctOptions('area'),
            ],
            'categoryOptions' => MasterDataRecord::documentCategories(),
            'statusOptions' => MasterDataRecord::statuses(),
            'organizationSectionOptions' => $this->organizationSectionOptions(),
            'summary' => $this->summary(),
            'filteredRecordCount' => $filteredRecordCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRecord($request);
        $validated['created_by'] = $request->user()?->id;

        MasterDataRecord::create($validated);

        return redirect()
            ->route('admin.master-data', ['document_category' => $validated['document_category']])
            ->with('success', 'Master data berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        MasterDataRecord $masterDataRecord,
        MasterDataStatusService $statusService,
        MasterDataUsageService $usageService
    ): RedirectResponse {
        $validated = $this->validateRecord($request, $masterDataRecord);
        $status = $validated['status'];
        unset($validated['status']);

        if (
            $masterDataRecord->status !== 'inactive'
            && $status === 'inactive'
            && $usageService->isInUse($masterDataRecord)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'status' => 'Equipment tidak dapat dinonaktifkan karena masih digunakan oleh draft/submission aktif.',
                ]);
        }

        DB::transaction(function () use ($masterDataRecord, $validated, $status, $statusService, $request): void {
            $masterDataRecord->fill($validated)->save();
            $statusService->setStatus(
                $masterDataRecord,
                $status,
                MasterDataStatusService::SOURCE_MANUAL_ADMIN,
                $request->user()
            );
        });

        return back()->with('success', 'Master data berhasil diperbarui.');
    }

    public function bulkStatus(
        Request $request,
        MasterDataStatusService $statusService,
        MasterDataUsageService $usageService
    ): RedirectResponse {
        $validated = $request->validate([
            'record_ids' => ['required', 'array', 'min:1'],
            'record_ids.*' => ['integer', 'exists:master_data_records,id'],
            'status' => ['required', Rule::in(array_keys(MasterDataRecord::statuses()))],
        ], [
            'record_ids.required' => 'Pilih minimal satu functional location terlebih dahulu.',
            'record_ids.min' => 'Pilih minimal satu functional location terlebih dahulu.',
        ]);

        $records = MasterDataRecord::whereIn('id', $validated['record_ids'])->get();
        $partition = $validated['status'] === 'inactive'
            ? $usageService->partition($records)
            : ['eligible' => $records, 'protected' => collect()];
        $recordsToUpdate = $partition['eligible']
            ->filter(fn (MasterDataRecord $record) => $record->status !== $validated['status']);
        $skipped = $partition['protected']->count();
        $label = MasterDataRecord::statuses()[$validated['status']];

        DB::transaction(function () use ($recordsToUpdate, $validated, $statusService, $request): void {
            $recordsToUpdate->each(function (MasterDataRecord $record) use ($validated, $statusService, $request): void {
                $statusService->setStatus(
                    $record,
                    $validated['status'],
                    MasterDataStatusService::SOURCE_BULK_SELECTED,
                    $request->user()
                );
            });
        });

        $message = "{$recordsToUpdate->count()} master data berhasil diubah menjadi {$label}.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati karena masih digunakan oleh draft/submission aktif.";
        }

        return back()->with('success', $message);
    }

    public function bulkFilteredStatus(
        Request $request,
        MasterDataStatusService $statusService,
        MasterDataUsageService $usageService
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(MasterDataRecord::statuses()))],
            'document_category' => ['nullable', Rule::in(['all', ...array_keys(MasterDataRecord::documentCategories())])],
            'year' => ['nullable', 'string', 'max:10'],
            'plant' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'current_status' => ['nullable', Rule::in(['all', ...array_keys(MasterDataRecord::statuses())])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $filters = [
            'document_category' => $validated['document_category'] ?? 'all',
            'year' => $validated['year'] ?? 'all',
            'plant' => $validated['plant'] ?? 'all',
            'area' => $validated['area'] ?? 'all',
            'status' => $validated['current_status'] ?? 'all',
            'search' => $validated['search'] ?? null,
        ];

        $records = $this->filteredRecordsQuery($filters)->get();
        $matched = $records->count();
        $label = MasterDataRecord::statuses()[$validated['status']];

        if ($matched === 0) {
            return back()->with('success', 'Tidak ada master data yang cocok dengan filter saat ini.');
        }

        $partition = $validated['status'] === 'inactive'
            ? $usageService->partition($records)
            : ['eligible' => $records, 'protected' => collect()];
        $recordsToUpdate = $partition['eligible']
            ->filter(fn (MasterDataRecord $record) => $record->status !== $validated['status']);
        $skipped = $partition['protected']->count();

        DB::transaction(function () use ($recordsToUpdate, $validated, $statusService, $request): void {
            $recordsToUpdate->each(function (MasterDataRecord $record) use ($validated, $statusService, $request): void {
                $statusService->setStatus(
                    $record,
                    $validated['status'],
                    MasterDataStatusService::SOURCE_BULK_FILTERED,
                    $request->user()
                );
            });
        });

        $message = "{$recordsToUpdate->count()} master data hasil filter berhasil diubah menjadi {$label}.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati karena masih digunakan oleh draft/submission aktif.";
        }

        return back()->with('success', $message);
    }

    public function updateInspectionStatus(
        Request $request,
        MasterDataRecord $masterDataRecord,
        MasterDataInspectionStatusService $inspectionStatus
    ): JsonResponse
    {
        $validated = $request->validate([
            'inspection_status' => ['nullable', Rule::in(array_keys(MasterDataRecord::inspectionStatuses()))],
        ]);

        $inspectionStatus->setStatus(
            $masterDataRecord,
            $validated['inspection_status'] ?? null,
            MasterDataInspectionStatusService::SOURCE_MANUAL_ADMIN,
            $request->user()
        );

        return response()->json([
            'status' => $masterDataRecord->inspection_status,
            'label' => $masterDataRecord->inspection_status
                ? MasterDataRecord::inspectionStatuses()[$masterDataRecord->inspection_status]
                : 'Pilih Status',
        ]);
    }

    public function destroy(MasterDataRecord $masterDataRecord): RedirectResponse
    {
        $masterDataRecord->delete();

        return back()->with('success', 'Master data berhasil dihapus.');
    }

    private function validateRecord(Request $request, ?MasterDataRecord $record = null): array
    {
        $validated = $request->validate([
            'document_category' => ['required', Rule::in(array_keys(MasterDataRecord::documentCategories()))],
            'year' => ['nullable', 'string', 'max:10'],
            'func_location' => [
                'required',
                'string',
                'max:255',
                Rule::unique('master_data_records', 'func_location')
                    ->where(fn ($query) => $query->where('document_category', $request->input('document_category')))
                    ->ignore($record?->id),
            ],
            'equipment_no' => ['nullable', 'string', 'max:80'],
            'section_no' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'plant' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'max:255'],
            'organization_section_id' => ['nullable', 'integer', Rule::exists('organization_sections', 'id')],
            'status' => ['required', Rule::in(array_keys(MasterDataRecord::statuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['equipment_no'] = $this->nullableEquipmentNo($validated['equipment_no'] ?? null);
        if ($validated['document_category'] !== MasterDataRecord::CATEGORY_COMMISSIONING) {
            $validated['organization_section_id'] = null;
        }

        return $validated;
    }

    private function filtersFromRequest(Request $request): array
    {
        return [
            'document_category' => $request->input('document_category', 'all'),
            'year' => $request->input('year', 'all'),
            'plant' => $request->input('plant', 'all'),
            'area' => $request->input('area', 'all'),
            'status' => $request->input('status', 'all'),
            'search' => $request->input('search'),
        ];
    }

    private function filteredRecordsQuery(array $filters)
    {
        return MasterDataRecord::query()
            ->when(($filters['document_category'] ?? 'all') !== 'all', fn ($query) => $query->where('document_category', $filters['document_category']))
            ->when(($filters['year'] ?? 'all') !== 'all', fn ($query) => $query->where('year', $filters['year']))
            ->when(($filters['plant'] ?? 'all') !== 'all', fn ($query) => $query->where('plant', $filters['plant']))
            ->when(($filters['area'] ?? 'all') !== 'all', fn ($query) => $query->where('area', $filters['area']))
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('status', $filters['status']))
            ->search($filters['search'] ?? null);
    }

    private function nullableEquipmentNo(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-' || mb_strtoupper($value) === 'N/A') {
            return null;
        }

        return $value;
    }

    private function distinctOptions(string $column): array
    {
        return MasterDataRecord::query()
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    private function summary(): array
    {
        $total = MasterDataRecord::count();

        return [
            'total' => $total,
            'qc' => MasterDataRecord::where('document_category', MasterDataRecord::CATEGORY_QC)->count(),
            'commissioning' => MasterDataRecord::where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)->count(),
            'active' => MasterDataRecord::where('status', 'active')->count(),
            'active_qc' => MasterDataRecord::where('document_category', MasterDataRecord::CATEGORY_QC)->where('status', 'active')->count(),
            'active_commissioning' => MasterDataRecord::where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING)->where('status', 'active')->count(),
        ];
    }

    private function organizationSectionOptions(): array
    {
        return OrganizationSection::query()
            ->active()
            ->orderBy('department')
            ->orderBy('unit_kerja')
            ->orderBy('section')
            ->get()
            ->map(fn (OrganizationSection $section) => [
                'id' => $section->id,
                'label' => $section->section,
                'meta' => collect([$section->unit_kerja, $section->department])->filter()->implode(' - '),
            ])
            ->all();
    }
}
