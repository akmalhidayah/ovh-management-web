<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use App\Services\MasterDataInspectionStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filtersFromRequest($request);

        $records = $this->filteredRecordsQuery($filters)
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
            'summary' => $this->summary(),
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

    public function update(Request $request, MasterDataRecord $masterDataRecord): RedirectResponse
    {
        $validated = $this->validateRecord($request, $masterDataRecord);

        $masterDataRecord->update($validated);

        return back()->with('success', 'Master data berhasil diperbarui.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'record_ids' => ['required', 'array', 'min:1'],
            'record_ids.*' => ['integer', 'exists:master_data_records,id'],
            'status' => ['required', Rule::in(array_keys(MasterDataRecord::statuses()))],
        ], [
            'record_ids.required' => 'Pilih minimal satu functional location terlebih dahulu.',
            'record_ids.min' => 'Pilih minimal satu functional location terlebih dahulu.',
        ]);

        $updated = MasterDataRecord::whereIn('id', $validated['record_ids'])
            ->update(['status' => $validated['status']]);

        $label = MasterDataRecord::statuses()[$validated['status']];

        return back()->with('success', "{$updated} master data berhasil diubah menjadi {$label}.");
    }

    public function bulkFilteredStatus(Request $request): RedirectResponse
    {
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

        $query = $this->filteredRecordsQuery($filters);
        $matched = (clone $query)->count();
        $label = MasterDataRecord::statuses()[$validated['status']];

        if ($matched === 0) {
            return back()->with('success', 'Tidak ada master data yang cocok dengan filter saat ini.');
        }

        $query->update(['status' => $validated['status']]);

        return back()->with('success', "{$matched} master data hasil filter berhasil diubah menjadi {$label}.");
    }

    public function updateInspectionStatus(
        Request $request,
        MasterDataRecord $masterDataRecord,
        MasterDataInspectionStatusService $inspectionStatus
    ): JsonResponse
    {
        $validated = $request->validate([
            'inspection_status' => ['required', Rule::in(array_keys(MasterDataRecord::inspectionStatuses()))],
        ]);

        $inspectionStatus->setStatus(
            $masterDataRecord,
            $validated['inspection_status'],
            MasterDataInspectionStatusService::SOURCE_MANUAL_ADMIN,
            $request->user()
        );

        return response()->json([
            'status' => $masterDataRecord->inspection_status,
            'label' => MasterDataRecord::inspectionStatuses()[$masterDataRecord->inspection_status],
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
            'status' => ['required', Rule::in(array_keys(MasterDataRecord::statuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['equipment_no'] = $this->nullableEquipmentNo($validated['equipment_no'] ?? null);

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
        ];
    }
}
