<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
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
            'status' => $request->input('status', 'all') ?: 'all',
            'year' => $request->input('year', 'all') ?: 'all',
            'plant' => $request->input('plant', 'all') ?: 'all',
            'area' => $request->input('area', 'all') ?: 'all',
            'search' => trim((string) $request->input('search')),
        ];

        $allRows = self::submissionRows()
            ->filter(fn (object $row) => self::matchesFilters($row, $filters))
            ->sortByDesc(fn (object $row) => $row->submitted_at?->timestamp ?? 0)
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $submissions = new LengthAwarePaginator(
            $allRows->forPage($page, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $page,
            [
                'path' => route('admin.qc'),
                'query' => $request->query(),
            ]
        );

        return [
            'pageTitle' => 'Inspection & Commissioning',
            'submissions' => $submissions,
            'statusLabels' => self::statusLabels(),
            'summary' => self::summary($allRows),
            'charts' => [
                'overall' => self::areaChartData(self::chartRows('all', $filters)),
                'qc' => self::areaChartData(self::chartRows('qc', $filters)),
                'commissioning' => self::areaChartData(self::chartRows('commissioning', $filters)),
            ],
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
                    ->with(['user'])
                    ->submitted()
                    ->latest('submitted_at')
                    ->get()
                    ->map(fn (QcFormSubmission $submission) => self::qcRow($submission))
            );
        }

        if ($type === null || $type === 'commissioning') {
            $rows = $rows->merge(
                CommissioningFormSubmission::query()
                    ->with(['user'])
                    ->latest('submitted_at')
                    ->get()
                    ->map(fn (CommissioningFormSubmission $submission) => self::commissioningRow($submission))
            );
        }

        return $rows;
    }

    private static function chartRows(string $type, array $filters): Collection
    {
        $chartFilters = array_merge($filters, ['type' => $type]);

        return self::submissionRows()
            ->filter(fn (object $row) => self::matchesFilters($row, $chartFilters))
            ->values();
    }

    private static function qcRow(QcFormSubmission $submission): object
    {
        return (object) [
            'type' => 'qc',
            'type_label' => 'QC',
            'model' => $submission,
            'form_number' => $submission->form_number,
            'year' => $submission->year ?: $submission->submitted_at?->format('Y'),
            'plant' => $submission->plant,
            'area' => $submission->area,
            'equipment' => $submission->equipment,
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
            'plant' => $header['plant'] ?? null,
            'area' => $submission->area,
            'equipment' => $submission->equipment,
            'user_name' => $submission->user?->name,
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at,
            'pdf_route' => $submission->status === 'submitted' ? route('admin.commissioning.submissions.pdf', $submission) : null,
        ];
    }

    private static function matchesFilters(object $row, array $filters): bool
    {
        if ($filters['type'] !== 'all' && $row->type !== $filters['type']) {
            return false;
        }

        if ($filters['status'] !== 'all' && $row->status !== $filters['status']) {
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
            $row->area,
            $row->plant,
            $row->user_name,
            $row->type_label,
        ]));

        return str_contains(mb_strtolower($haystack), mb_strtolower($filters['search']));
    }

    private static function summary(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'submitted' => $rows->where('status', 'submitted')->count(),
            'approved' => $rows->where('status', 'approved')->count(),
            'revision' => $rows->where('status', 'revision')->count(),
        ];
    }

    private static function filterOptions(): array
    {
        $rows = self::submissionRows();

        return [
            'years' => $rows->pluck('year')->filter()->unique()->sortDesc()->values(),
            'plants' => $rows->pluck('plant')->filter()->unique()->sort()->values(),
            'areas' => $rows->pluck('area')->filter()->unique()->sort()->values(),
        ];
    }

    private static function areaChartData(Collection $rows): array
    {
        $total = max($rows->count(), 1);
        $groups = $rows
            ->groupBy(fn (object $row) => filled($row->area) ? $row->area : 'Tanpa Area')
            ->map(function (Collection $areaRows, string $area) use ($total) {
                $count = $areaRows->count();

                return [
                    'area' => $area,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1),
                    'plants' => $areaRows->pluck('plant')->filter()->unique()->sort()->values()->all(),
                    'years' => $areaRows->pluck('year')->filter()->unique()->sortDesc()->values()->all(),
                ];
            })
            ->sortByDesc('count')
            ->take(8);

        return [
            'labels' => $groups->pluck('area')->values(),
            'data' => $groups->pluck('percentage')->values(),
            'counts' => $groups->pluck('count')->values(),
            'total' => $rows->count(),
            'meta' => $groups->values(),
        ];
    }

    private static function statusLabels(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
        ];
    }
}
