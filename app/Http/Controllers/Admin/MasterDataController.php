<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'document_category' => $request->query('document_category', 'all'),
            'year' => $request->query('year', 'all'),
            'plant' => $request->query('plant', 'all'),
            'area' => $request->query('area', 'all'),
            'status' => $request->query('status', 'all'),
            'search' => $request->query('search'),
        ];

        $baseQuery = MasterDataRecord::query();

        $records = (clone $baseQuery)
            ->when($filters['document_category'] !== 'all', fn ($query) => $query->where('document_category', $filters['document_category']))
            ->when($filters['year'] !== 'all', fn ($query) => $query->where('year', $filters['year']))
            ->when($filters['plant'] !== 'all', fn ($query) => $query->where('plant', $filters['plant']))
            ->when($filters['area'] !== 'all', fn ($query) => $query->where('area', $filters['area']))
            ->when($filters['status'] !== 'all', fn ($query) => $query->where('status', $filters['status']))
            ->search($filters['search'])
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

    public function destroy(MasterDataRecord $masterDataRecord): RedirectResponse
    {
        $masterDataRecord->delete();

        return back()->with('success', 'Master data berhasil dihapus.');
    }

    private function validateRecord(Request $request, ?MasterDataRecord $record = null): array
    {
        return $request->validate([
            'document_category' => ['required', Rule::in(array_keys(MasterDataRecord::documentCategories()))],
            'year' => ['nullable', 'string', 'max:10'],
            'func_location' => ['required', 'string', 'max:255'],
            'equipment_no' => [
                'required',
                'string',
                'max:80',
                Rule::unique('master_data_records', 'equipment_no')
                    ->where(fn ($query) => $query->where('document_category', $request->input('document_category')))
                    ->ignore($record?->id),
            ],
            'section_no' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'plant' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(MasterDataRecord::statuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
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
