<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminInspectionSubmissionPageData
{
    public static function make(Request $request, string $defaultType = 'all'): array
    {
        $filters = [
            'type' => self::validType($request->input('type', $defaultType)),
            'year' => $request->input('year', 'all') ?: 'all',
            'plant' => $request->input('plant', 'all') ?: 'all',
            'search' => trim((string) $request->input('search')),
        ];

        $allRows = self::submissionRows()
            ->filter(fn (object $row) => self::matchesFilters($row, $filters))
            ->sortByDesc(fn (object $row) => $row->submitted_at?->timestamp ?? 0)
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $pageRoute = $filters['type'] === 'commissioning' ? 'admin.commissioning' : 'admin.qc';
        $submissions = new LengthAwarePaginator(
            $allRows->forPage($page, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $page,
            [
                'path' => route($pageRoute),
                'query' => $request->query(),
            ]
        );

        $pageTitle = match ($filters['type']) {
            'qc' => 'Quality Control',
            'commissioning' => 'Commissioning',
            default => 'QC & Commissioning',
        };

        return [
            'pageTitle' => $pageTitle,
            'submissions' => $submissions,
            'statusLabels' => self::statusLabels(),
            'inspectionMetrics' => self::inspectionMetrics($filters),
            'filterOptions' => self::filterOptions(),
            'filters' => $filters,
        ];
    }

    private static function validType(string $type): string
    {
        return in_array($type, ['all', 'qc', 'commissioning'], true) ? $type : 'all';
    }

    private static function submissionRows(?string $type = null): Collection
    {
        $rows = collect();

        if ($type === null || $type === 'qc') {
            $rows = $rows->merge(
                QcFormSubmission::query()
                    ->with(['user', 'approvalFlow.steps'])
                    ->submitted()
                    ->latest('submitted_at')
                    ->get()
                    ->map(fn (QcFormSubmission $submission) => self::qcRow($submission))
            );
        }

        if ($type === null || $type === 'commissioning') {
            $rows = $rows->merge(
                CommissioningFormSubmission::query()
                    ->with(['user', 'approvalFlow.steps'])
                    ->whereIn('status', ['submitted', 'pending_approval', 'approved', 'revision_required', 'rejected', 'cancelled'])
                    ->latest('submitted_at')
                    ->get()
                    ->map(fn (CommissioningFormSubmission $submission) => self::commissioningRow($submission))
            );
        }

        return $rows;
    }

    private static function qcRow(QcFormSubmission $submission): object
    {
        $generalInfo = $submission->general_info ?? [];

        return (object) [
            'type' => 'qc',
            'type_label' => 'QC',
            'model' => $submission,
            'form_number' => $submission->form_number,
            'year' => $submission->year ?: $submission->submitted_at?->format('Y'),
            'plant' => self::firstFilled($submission->plant, $generalInfo['plant'] ?? null, $generalInfo['ovh_plant'] ?? null),
            'area' => self::firstFilled($submission->area, $generalInfo['area'] ?? null),
            'equipment_key' => self::firstFilled(
                $generalInfo['id_equipment'] ?? null,
                $generalInfo['equipment_no'] ?? null,
                $generalInfo['master_data_record_id'] ?? null
            ),
            'equipment' => self::firstFilled(
                $submission->equipment,
                $generalInfo['name_equipment'] ?? null,
                $generalInfo['alat'] ?? null,
                $generalInfo['equipment'] ?? null
            ),
            'user_name' => $submission->user?->name,
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at,
            'pdf_route' => route('admin.qc.submissions.pdf', $submission),
        ];
    }

    private static function commissioningRow(CommissioningFormSubmission $submission): object
    {
        $header = $submission->header_data ?? [];

        return (object) [
            'type' => 'commissioning',
            'type_label' => 'Commissioning',
            'model' => $submission,
            'form_number' => $submission->form_number,
            'year' => $submission->year ?: $submission->submitted_at?->format('Y'),
            'plant' => self::firstFilled($header['plant'] ?? null, $header['ovh_plant'] ?? null),
            'area' => self::firstFilled($submission->area, $header['area'] ?? null),
            'equipment_key' => self::firstFilled(
                $submission->equipment_no,
                $header['id_equipment'] ?? null,
                $header['master_data_record_id'] ?? null,
                $submission->functional_location,
                $submission->tag_num
            ),
            'equipment' => self::firstFilled(
                $submission->equipment,
                $header['name_equipment'] ?? null,
                $header['alat'] ?? null,
                $header['equipment'] ?? null
            ),
            'user_name' => $submission->user?->name,
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at,
            'pdf_route' => $submission->status !== 'draft' ? route('admin.commissioning.submissions.pdf', $submission) : null,
        ];
    }

    private static function firstFilled(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private static function matchesFilters(object $row, array $filters): bool
    {
        if ($filters['type'] !== 'all' && $row->type !== $filters['type']) {
            return false;
        }

        foreach (['year', 'plant'] as $field) {
            if ($filters[$field] !== 'all' && (string) $row->{$field} !== (string) $filters[$field]) {
                return false;
            }
        }

        if ($filters['search'] === '') {
            return true;
        }

        $haystack = implode(' ', array_filter([
            $row->form_number,
            $row->equipment,
            $row->area,
            $row->plant,
            $row->user_name,
            $row->type_label,
        ]));

        return str_contains(mb_strtolower($haystack), mb_strtolower($filters['search']));
    }

    private static function filterOptions(): array
    {
        $rows = self::submissionRows();
        $masterRows = self::masterDataRows();

        return [
            'years' => $rows->pluck('year')->merge($masterRows->pluck('year'))->filter()->unique()->sortDesc()->values(),
            'plants' => $rows->pluck('plant')->merge($masterRows->pluck('plant'))->filter()->unique()->sort()->values(),
        ];
    }

    private static function inspectionMetrics(array $filters): ?array
    {
        return match ($filters['type']) {
            'qc' => self::metricsForType(
                $filters,
                MasterDataRecord::CATEGORY_QC,
                fn (array $filters): Collection => self::qcDashboardSubmissionRows($filters),
                'QC'
            ),
            'commissioning' => self::metricsForType(
                $filters,
                MasterDataRecord::CATEGORY_COMMISSIONING,
                fn (array $filters): Collection => self::commissioningDashboardSubmissionRows($filters),
                'Commissioning'
            ),
            default => null,
        };
    }

    private static function metricsForType(array $filters, string $documentCategory, callable $submissionRowsResolver, string $label): array
    {
        $submissionRows = $submissionRowsResolver($filters);
        $processKeys = $submissionRows
            ->reject(fn (object $row) => $row->status === 'draft')
            ->map(fn (object $row) => self::normalizeEquipmentKey($row->equipment_key ?? $row->equipment))
            ->filter()
            ->unique()
            ->values();
        $ongoingKeys = $submissionRows
            ->where('status', 'draft')
            ->map(fn (object $row) => self::normalizeEquipmentKey($row->equipment_key ?? $row->equipment))
            ->filter()
            ->unique()
            ->diff($processKeys)
            ->values();

        $masterRows = self::filteredMasterRows($filters, $submissionRows, $documentCategory);
        $areaRows = $masterRows
            ->groupBy(fn (MasterDataRecord $record) => filled($record->area) ? $record->area : 'Tanpa Area')
            ->map(function (Collection $records, string $area) use ($processKeys, $ongoingKeys): array {
                $equipmentRecords = $records
                    ->unique(fn (MasterDataRecord $record) => self::normalizeEquipmentKey($record->equipment_no) ?: (string) $record->id)
                    ->values();
                $processCount = $equipmentRecords
                    ->filter(fn (MasterDataRecord $record) => self::recordMatchesEquipmentKeys($record, $processKeys))
                    ->count();
                $ongoingCount = $equipmentRecords
                    ->filter(fn (MasterDataRecord $record) => self::recordMatchesEquipmentKeys($record, $ongoingKeys)
                        && ! self::recordMatchesEquipmentKeys($record, $processKeys))
                    ->count();
                $equipmentCount = $equipmentRecords->count();

                return [
                    'area' => $area,
                    'equipment' => $equipmentCount,
                    'ongoing' => $ongoingCount,
                    'process' => $processCount,
                    'progress' => $equipmentCount > 0 ? round(($processCount / $equipmentCount) * 100, 1) : 0,
                ];
            })
            ->sortKeys()
            ->values();

        $process = $areaRows->sum('process');
        $ongoing = $areaRows->sum('ongoing');
        $total = $process + $ongoing;
        $percentage = $total > 0 ? round(($process / $total) * 100, 1) : 0;

        return [
            'cards' => [
                'total' => $total,
                'process' => $process,
                'ongoing' => $ongoing,
                'percentage' => $percentage,
            ],
            'areaRows' => $areaRows,
            'chart' => [
                'labels' => $areaRows->pluck('area')->values(),
                'data' => $areaRows->pluck('progress')->values(),
            ],
            'label' => $label,
        ];
    }

    private static function qcDashboardSubmissionRows(array $filters): Collection
    {
        return QcFormSubmission::query()
            ->with('user')
            ->latest('updated_at')
            ->get()
            ->map(fn (QcFormSubmission $submission) => self::qcRow($submission))
            ->filter(fn (object $row) => self::matchesFilters($row, $filters))
            ->values();
    }

    private static function commissioningDashboardSubmissionRows(array $filters): Collection
    {
        return CommissioningFormSubmission::query()
            ->with('user')
            ->latest('updated_at')
            ->get()
            ->map(fn (CommissioningFormSubmission $submission) => self::commissioningRow($submission))
            ->filter(fn (object $row) => self::matchesFilters($row, $filters))
            ->values();
    }

    private static function masterRowsByCategory(string $documentCategory): Collection
    {
        return MasterDataRecord::query()
            ->where('document_category', $documentCategory)
            ->where('status', 'active')
            ->get();
    }

    private static function masterDataRows(): Collection
    {
        return MasterDataRecord::query()
            ->where('status', 'active')
            ->get();
    }

    private static function filteredMasterRows(array $filters, Collection $submissionRows, string $documentCategory): Collection
    {
        $submissionKeys = $submissionRows
            ->map(fn (object $row) => self::normalizeEquipmentKey($row->equipment_key ?? $row->equipment))
            ->filter()
            ->unique()
            ->values();
        $search = mb_strtolower($filters['search']);

        return self::masterRowsByCategory($documentCategory)
            ->filter(function (MasterDataRecord $record) use ($filters, $search, $submissionKeys): bool {
                if ($filters['year'] !== 'all' && (string) $record->year !== (string) $filters['year']) {
                    return false;
                }

                if ($filters['plant'] !== 'all' && (string) $record->plant !== (string) $filters['plant']) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                $haystack = mb_strtolower(implode(' ', array_filter([
                    $record->func_location,
                    $record->equipment_no,
                    $record->section_no,
                    $record->description,
                    $record->plant,
                    $record->area,
                ])));

                return str_contains($haystack, $search) || self::recordMatchesEquipmentKeys($record, $submissionKeys);
            })
            ->values();
    }

    private static function recordMatchesEquipmentKeys(MasterDataRecord $record, Collection $equipmentKeys): bool
    {
        return collect([
            $record->equipment_no,
            $record->description,
            $record->func_location,
            $record->section_no,
            $record->id,
        ])
            ->map(fn (mixed $value) => self::normalizeEquipmentKey($value))
            ->filter()
            ->intersect($equipmentKeys)
            ->isNotEmpty();
    }

    private static function normalizeEquipmentKey(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtolower($value);
    }

    private static function statusLabels(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Menunggu Review',
            'pending_approval' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
            'revision_required' => 'Perlu Revisi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
    }
}
