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
            'area' => $request->input('area', 'all') ?: 'all',
            'work_status' => $request->input('work_status', 'all') ?: 'all',
            'sort' => self::validSort($request->input('sort', 'default')),
            'search' => trim((string) $request->input('search')),
        ];

        $allRows = $filters['type'] === 'commissioning'
            ? self::commissioningIndexRows($filters)
            : self::submissionRows()
                ->filter(fn (object $row) => self::matchesFilters($row, $filters))
                ->values();
        $allRows = self::sortIndexRows($allRows, $filters);

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
            'inspectionMetrics' => self::inspectionMetrics(self::withoutSearch($filters)),
            'filterOptions' => self::filterOptions(),
            'filters' => $filters,
        ];
    }

    private static function validType(string $type): string
    {
        return in_array($type, ['all', 'qc', 'commissioning'], true) ? $type : 'all';
    }

    private static function validSort(?string $sort): string
    {
        return in_array($sort, ['default', 'name_asc', 'name_desc', 'area_asc', 'area_desc'], true)
            ? $sort
            : 'default';
    }

    private static function submissionRows(?string $type = null): Collection
    {
        $rows = collect();

        if ($type === null || $type === 'qc') {
            $rows = $rows->merge(
                QcFormSubmission::query()
                    ->with(['user', 'approvalFlow.steps', 'rows'])
                    ->whereIn('status', ['draft', 'submitted', 'pending_approval', 'approved', 'revision', 'revision_required', 'rejected', 'cancelled'])
                    ->latest('submitted_at')
                    ->get()
                    ->map(fn (QcFormSubmission $submission) => self::qcRow($submission))
            );
        }

        if ($type === null || $type === 'commissioning') {
            $rows = $rows->merge(
                CommissioningFormSubmission::query()
                    ->with(['user', 'approvalFlow.steps'])
                    ->whereIn('status', ['draft', 'submitted', 'pending_approval', 'approved', 'revision_required', 'rejected', 'cancelled'])
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

        $row = (object) [
            'type' => 'qc',
            'type_label' => 'QC',
            'model' => $submission,
            'form_number' => $submission->form_number,
            'year' => $submission->year ?: $submission->submitted_at?->format('Y'),
            'plant' => self::firstFilled($submission->plant, $generalInfo['plant'] ?? null, $generalInfo['ovh_plant'] ?? null),
            'area' => self::firstFilled($submission->area, $generalInfo['area'] ?? null),
            'master_data_record_id' => $generalInfo['master_data_record_id'] ?? null,
            'functional_location' => $generalInfo['functional_location'] ?? null,
            'equipment_no' => self::firstFilled($generalInfo['id_equipment'] ?? null, $generalInfo['equipment_no'] ?? null),
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
            'user_photo_url' => $submission->user?->profilePhotoUrl(),
            'user_initials' => self::userInitials($submission->user?->name),
            'status' => $submission->status,
            'work_status' => self::workStatus($submission->status),
            'submitted_at' => $submission->submitted_at,
            'pdf_route' => $submission->status !== 'draft' ? route('admin.qc.submissions.pdf', $submission) : null,
            'remarks' => self::qcRemarks($submission),
        ];

        $row->remarks_count = count($row->remarks);

        return self::withMasterInspectionStatus($row);
    }

    private static function commissioningRow(CommissioningFormSubmission $submission): object
    {
        $header = $submission->header_data ?? [];

        $row = (object) [
            'type' => 'commissioning',
            'type_label' => 'Commissioning',
            'model' => $submission,
            'form_number' => $submission->form_number,
            'year' => $submission->year ?: $submission->submitted_at?->format('Y'),
            'plant' => self::firstFilled($header['plant'] ?? null, $header['ovh_plant'] ?? null),
            'area' => self::firstFilled($submission->area, $header['area'] ?? null),
            'master_data_record_id' => $header['master_data_record_id'] ?? null,
            'functional_location' => self::firstFilled($submission->functional_location, $header['functional_location'] ?? null),
            'equipment_no' => self::firstFilled($submission->equipment_no, $header['id_equipment'] ?? null),
            'section_no' => $submission->tag_num,
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
            'user_photo_url' => $submission->user?->profilePhotoUrl(),
            'user_initials' => self::userInitials($submission->user?->name),
            'status' => $submission->status,
            'work_status' => self::workStatus($submission->status),
            'submitted_at' => $submission->submitted_at,
            'pdf_route' => $submission->status !== 'draft' ? route('admin.commissioning.submissions.pdf', $submission) : null,
            'remarks' => self::commissioningRemarks($submission),
        ];

        $row->remarks_count = count($row->remarks);

        return self::withMasterInspectionStatus($row);
    }

    private static function commissioningIndexRows(array $filters): Collection
    {
        $submissionRows = self::submissionRows('commissioning')
            ->filter(fn (object $row) => self::matchesFilters($row, self::withoutSearch($filters)))
            ->sortByDesc(fn (object $row) => $row->submitted_at?->timestamp ?? 0)
            ->values();
        $searchMatchedSubmissionRows = $submissionRows
            ->filter(fn (object $row) => self::matchesFilters($row, $filters))
            ->values();

        return self::filteredMasterRows($filters, $searchMatchedSubmissionRows, MasterDataRecord::CATEGORY_COMMISSIONING)
            ->map(function (MasterDataRecord $record) use ($submissionRows): object {
                $submissionRow = $submissionRows->first(
                    fn (object $row) => self::commissioningSubmissionMatchesRecord($row, $record)
                );

                return self::commissioningMasterRow($record, $submissionRow);
            })
            ->filter(fn (object $row) => self::matchesWorkStatusFilter($row, $filters))
            ->values();
    }

    private static function withoutSearch(array $filters): array
    {
        $filters['search'] = '';

        return $filters;
    }

    private static function sortIndexRows(Collection $rows, array $filters): Collection
    {
        return match ($filters['sort'] ?? 'default') {
            'name_asc' => $rows->sort(fn (object $a, object $b): int => self::compareRowText(
                self::rowSubmissionName($a),
                self::rowSubmissionName($b),
            ))->values(),
            'name_desc' => $rows->sort(fn (object $a, object $b): int => self::compareRowText(
                self::rowSubmissionName($b),
                self::rowSubmissionName($a),
            ))->values(),
            'area_asc' => $rows->sort(fn (object $a, object $b): int => self::compareRowText(
                self::rowAreaSortKey($a),
                self::rowAreaSortKey($b),
            ))->values(),
            'area_desc' => $rows->sort(fn (object $a, object $b): int => self::compareRowText(
                self::rowAreaSortKey($b),
                self::rowAreaSortKey($a),
            ))->values(),
            default => ($filters['type'] ?? null) === 'commissioning'
                ? $rows->sort(fn (object $a, object $b): int => self::compareCommissioningIndexRows($a, $b))->values()
                : $rows->sortByDesc(fn (object $row) => $row->submitted_at?->timestamp ?? 0)->values(),
        };
    }

    private static function rowSubmissionName(object $row): string
    {
        return implode('|', [
            self::sortText($row->equipment ?? null),
            self::sortText($row->form_number ?? null),
            self::sortText($row->functional_location ?? null),
            self::sortText($row->equipment_no ?? null),
        ]);
    }

    private static function rowAreaSortKey(object $row): string
    {
        return implode('|', [
            self::sortText($row->area ?? null),
            self::sortText($row->plant ?? null),
            self::rowSubmissionName($row),
        ]);
    }

    private static function compareRowText(string $left, string $right): int
    {
        return strnatcasecmp($left, $right);
    }

    private static function sortText(mixed $value): string
    {
        $text = trim((string) $value);

        return $text === '' ? '~~~~' : mb_strtolower($text);
    }

    private static function compareCommissioningIndexRows(object $a, object $b): int
    {
        $rankComparison = self::commissioningIndexStatusRank($a) <=> self::commissioningIndexStatusRank($b);

        if ($rankComparison !== 0) {
            return $rankComparison;
        }

        $submittedAtComparison = ($b->submitted_at?->timestamp ?? 0) <=> ($a->submitted_at?->timestamp ?? 0);

        if ($submittedAtComparison !== 0) {
            return $submittedAtComparison;
        }

        return strnatcasecmp(
            implode('|', [$a->area ?? '', $a->functional_location ?? '', $a->equipment ?? '']),
            implode('|', [$b->area ?? '', $b->functional_location ?? '', $b->equipment ?? ''])
        );
    }

    private static function commissioningIndexStatusRank(object $row): int
    {
        return match ($row->work_status ?? null) {
            'close' => 0,
            'ongoing' => 1,
            default => 2,
        };
    }

    private static function commissioningMasterRow(MasterDataRecord $record, ?object $submissionRow): object
    {
        return (object) [
            'type' => 'commissioning',
            'type_label' => 'Commissioning',
            'model' => $submissionRow?->model,
            'form_number' => $submissionRow?->form_number,
            'year' => $record->year,
            'plant' => $record->plant,
            'area' => $record->area,
            'master_data_record_id' => $record->id,
            'functional_location' => $record->func_location,
            'equipment_no' => $record->equipment_no,
            'section_no' => $record->section_no,
            'equipment_key' => self::firstFilled($record->equipment_no, $record->func_location, $record->id),
            'equipment' => $record->description,
            'user_name' => $submissionRow?->user_name,
            'user_photo_url' => $submissionRow?->user_photo_url,
            'user_initials' => $submissionRow?->user_initials,
            'status' => $submissionRow?->status,
            'work_status' => self::effectiveWorkStatus($record, $submissionRow?->work_status),
            'submitted_at' => $submissionRow?->submitted_at,
            'pdf_route' => $submissionRow?->pdf_route,
            'remarks' => $submissionRow?->remarks ?? [],
            'remarks_count' => $submissionRow?->remarks_count ?? 0,
            'inspection_status_update_url' => route('admin.master-data.inspection-status', $record),
        ];
    }

    private static function commissioningSubmissionMatchesRecord(object $row, MasterDataRecord $record): bool
    {
        $rowMasterId = self::normalizeEquipmentKey($row->master_data_record_id ?? null);
        if ($rowMasterId !== null) {
            return $rowMasterId === self::normalizeEquipmentKey($record->id);
        }

        $rowFunctionalLocation = self::normalizeEquipmentKey($row->functional_location ?? null);
        if ($rowFunctionalLocation !== null) {
            return $rowFunctionalLocation === self::normalizeEquipmentKey($record->func_location);
        }

        $rowEquipmentNo = self::normalizeEquipmentKey($row->equipment_no ?? $row->equipment_key ?? null);
        if ($rowEquipmentNo !== null && filled($record->equipment_no)) {
            return $rowEquipmentNo === self::normalizeEquipmentKey($record->equipment_no);
        }

        return false;
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

    private static function userInitials(?string $name): string
    {
        $words = collect(preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return $words !== '' ? $words : 'U';
    }

    private static function matchesFilters(object $row, array $filters): bool
    {
        if ($filters['type'] !== 'all' && $row->type !== $filters['type']) {
            return false;
        }

        if (! self::matchesWorkStatusFilter($row, $filters)) {
            return false;
        }

        foreach (['year', 'plant', 'area'] as $field) {
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
            $row->functional_location ?? null,
            $row->equipment_no ?? null,
            $row->section_no ?? null,
            $row->area,
            $row->plant,
            $row->user_name,
            $row->type_label,
        ]));

        return self::matchesSearch($haystack, $filters['search']);
    }

    private static function matchesSearch(string $haystack, string $search): bool
    {
        $haystack = mb_strtolower($haystack);
        $search = mb_strtolower(trim($search));

        if ($search === '') {
            return true;
        }

        if (str_contains($haystack, $search)) {
            return true;
        }

        $normalizedHaystack = preg_replace('/[^a-z0-9]+/i', '', $haystack) ?? '';
        $normalizedSearch = preg_replace('/[^a-z0-9]+/i', '', $search) ?? '';

        return $normalizedSearch !== '' && str_contains(mb_strtolower($normalizedHaystack), mb_strtolower($normalizedSearch));
    }

    private static function matchesWorkStatusFilter(object $row, array $filters): bool
    {
        return ($filters['work_status'] ?? 'all') === 'all'
            || ($row->work_status ?? null) === $filters['work_status'];
    }

    private static function workStatus(?string $submissionStatus): string
    {
        return $submissionStatus === 'draft' ? 'ongoing' : 'close';
    }

    private static function effectiveWorkStatus(?MasterDataRecord $record, ?string $fallback): string
    {
        return in_array($record?->inspection_status, ['close', 'ongoing'], true)
            ? $record->inspection_status
            : ($fallback ?: 'not_started');
    }

    private static function withMasterInspectionStatus(object $row): object
    {
        $record = self::findMatchingMasterRecord($row);

        if ($record) {
            $row->master_data_record_id = $record->id;
            $row->work_status = self::effectiveWorkStatus($record, $row->work_status ?? null);
            $row->inspection_status_update_url = route('admin.master-data.inspection-status', $record);
        }

        return $row;
    }

    private static function findMatchingMasterRecord(object $row): ?MasterDataRecord
    {
        $query = MasterDataRecord::query()
            ->where('document_category', $row->type)
            ->where('status', 'active');

        if (filled($row->master_data_record_id ?? null)) {
            return (clone $query)->whereKey($row->master_data_record_id)->first();
        }

        if (filled($row->functional_location ?? null)) {
            return (clone $query)->where('func_location', $row->functional_location)->first();
        }

        if (filled($row->equipment_no ?? null)) {
            return (clone $query)->where('equipment_no', $row->equipment_no)->first();
        }

        return null;
    }

    private static function filterOptions(): array
    {
        $rows = self::submissionRows();
        $masterRows = self::masterDataRows();

        return [
            'years' => $rows->pluck('year')->merge($masterRows->pluck('year'))->filter()->unique()->sortDesc()->values(),
            'plants' => $rows->pluck('plant')->merge($masterRows->pluck('plant'))->filter()->unique()->sort()->values(),
            'areas' => $rows->pluck('area')->merge($masterRows->pluck('area'))->filter()->unique()->sort()->values(),
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
            ) + [
                'remarkForms' => self::qcRemarkFormCount($filters),
            ],
            'commissioning' => self::metricsForType(
                $filters,
                MasterDataRecord::CATEGORY_COMMISSIONING,
                fn (array $filters): Collection => self::commissioningDashboardSubmissionRows($filters),
                'Commissioning'
            ) + [
                'remarkForms' => self::commissioningRemarkFormCount($filters),
            ],
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
                    ->filter(fn (MasterDataRecord $record) => self::effectiveWorkStatusForMetrics($record, $processKeys, $ongoingKeys) === 'close')
                    ->count();
                $ongoingCount = $equipmentRecords
                    ->filter(fn (MasterDataRecord $record) => self::effectiveWorkStatusForMetrics($record, $processKeys, $ongoingKeys) === 'ongoing')
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
        $total = $areaRows->sum('equipment');
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
            ->with(['user', 'rows'])
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

                if ($filters['area'] !== 'all' && (string) $record->area !== (string) $filters['area']) {
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

                return self::matchesSearch($haystack, $search) || self::recordMatchesEquipmentKeys($record, $submissionKeys);
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

    private static function effectiveWorkStatusForMetrics(MasterDataRecord $record, Collection $processKeys, Collection $ongoingKeys): string
    {
        $fallback = self::recordMatchesEquipmentKeys($record, $processKeys)
            ? 'close'
            : (self::recordMatchesEquipmentKeys($record, $ongoingKeys) ? 'ongoing' : 'not_started');

        return self::effectiveWorkStatus($record, $fallback);
    }

    private static function normalizeEquipmentKey(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtolower($value);
    }

    private static function qcRemarkFormCount(array $filters): int
    {
        return self::qcDashboardSubmissionRows($filters)
            ->filter(fn (object $row) => ($row->remarks_count ?? 0) > 0)
            ->pluck('model.id')
            ->filter()
            ->unique()
            ->count();
    }

    private static function commissioningRemarkFormCount(array $filters): int
    {
        return self::commissioningDashboardSubmissionRows($filters)
            ->filter(fn (object $row) => ($row->remarks_count ?? 0) > 0)
            ->pluck('model.id')
            ->filter()
            ->unique()
            ->count();
    }

    private static function qcRemarks(QcFormSubmission $submission): array
    {
        $submission->loadMissing('rows');

        return $submission->rows
            ->filter(fn ($row) => filled($row->catatan))
            ->map(function ($row): array {
                $data = $row->row_data ?? [];

                return [
                    'section' => self::qcRemarkSection($row->block_type),
                    'row' => (string) self::firstFilled($data['no'] ?? null, $data['urutan'] ?? null, $row->order_no),
                    'item' => self::firstFilled(
                        $data['item_pengecekan'] ?? null,
                        $data['deskripsi'] ?? null,
                        $data['nama_welder'] ?? null,
                        $data['label'] ?? null,
                        $data['item'] ?? null,
                        $data['key'] ?? null
                    ),
                    'result' => self::firstFilled($row->status_value, $data['status'] ?? null, $data['result'] ?? null),
                    'text' => trim((string) $row->catatan),
                ];
            })
            ->values()
            ->all();
    }

    private static function qcRemarkSection(?string $blockType): string
    {
        return match ($blockType) {
            'brics_check' => 'QC Brics',
            'castable_check' => 'QC Castable Check',
            'castable_monitoring' => 'QC Castable Monitoring',
            'welding_welder' => 'QC Welding Welder',
            'welding_result' => 'QC Welding Result',
            'general' => 'QC General',
            default => 'QC',
        };
    }

    private static function commissioningRemarks(CommissioningFormSubmission $submission): array
    {
        $body = $submission->body_data ?? [];

        return collect()
            ->merge(self::remarksFromRows(
                $body['motor_test_rows'] ?? [],
                'Motor Test Report',
                ['remarks', 'remark']
            ))
            ->merge(self::remarksFromRows(
                $body['gearbox_test_rows'] ?? [],
                'Gearbox Test Report',
                ['remarks', 'remark']
            ))
            ->merge(self::remarksFromRows(
                $body['equipment_check_rows'] ?? [],
                'Equipment Check Data',
                ['remark', 'remarks'],
                true
            ))
            ->values()
            ->all();
    }

    private static function remarksFromRows(array $rows, string $section, array $keys, bool $includeItem = false): array
    {
        return collect($rows)
            ->map(function (array $row, int $index) use ($section, $keys, $includeItem): ?array {
                $text = self::firstFilled(...array_map(fn (string $key) => $row[$key] ?? null, $keys));

                if (blank($text)) {
                    return null;
                }

                return [
                    'section' => $section,
                    'row' => (string) ($row['no'] ?? $index + 1),
                    'item' => $includeItem ? self::firstFilled($row['item'] ?? null, $row['result'] ?? null) : null,
                    'result' => $includeItem ? ($row['result'] ?? null) : null,
                    'text' => trim((string) $text),
                ];
            })
            ->filter()
            ->values()
            ->all();
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
