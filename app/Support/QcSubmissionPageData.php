<?php

namespace App\Support;

use App\Http\Controllers\User\Qc\FormController as UserQcFormController;
use App\Models\QcFormSubmission;
use App\Models\QcFormTemplate;
use Illuminate\Http\Request;

class QcSubmissionPageData
{
    public static function make(Request $request, string $pageTitle = 'QC'): array
    {
        $status = $request->input('status');
        $templateId = $request->input('template_id');
        $year = $request->input('year');
        $plant = $request->input('plant');
        $area = $request->input('area');
        $search = trim((string) $request->input('search'));

        $submissions = QcFormSubmission::query()
            ->with(['template', 'user'])
            ->submitted()
            ->when($status && $status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($templateId && $templateId !== 'all', fn ($query) => $query->where('qc_form_template_id', $templateId))
            ->when($year && $year !== 'all', fn ($query) => $query->where('year', $year))
            ->when($plant && $plant !== 'all', fn ($query) => $query->where('plant', $plant))
            ->when($area && $area !== 'all', fn ($query) => $query->where('area', $area))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('form_number', 'like', "%{$search}%")
                        ->orWhere('report_no', 'like', "%{$search}%")
                        ->orWhere('equipment', 'like', "%{$search}%")
                        ->orWhere('pekerjaan', 'like', "%{$search}%")
                        ->orWhereHas('template', fn ($templateQuery) => $templateQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        $baseSubmitted = QcFormSubmission::query()->submitted();
        $statusCounts = (clone $baseSubmitted)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $templateIds = QcFormSubmission::query()
            ->submitted()
            ->whereNotNull('qc_form_template_id')
            ->distinct()
            ->pluck('qc_form_template_id');

        return [
            'pageTitle' => $pageTitle,
            'submissions' => $submissions,
            'statusLabels' => UserQcFormController::statusLabels(),
            'summary' => [
                'total' => (clone $baseSubmitted)->count(),
                'submitted' => (int) ($statusCounts['submitted'] ?? 0),
                'approved' => (int) ($statusCounts['approved'] ?? 0),
                'revision' => (int) ($statusCounts['revision'] ?? 0),
            ],
            'filterOptions' => [
                'years' => QcFormSubmission::query()->submitted()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
                'plants' => QcFormSubmission::query()->submitted()->whereNotNull('plant')->distinct()->orderBy('plant')->pluck('plant'),
                'areas' => QcFormSubmission::query()->submitted()->whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
                'templates' => QcFormTemplate::query()->whereIn('id', $templateIds)->orderBy('name')->get(['id', 'name']),
            ],
            'filters' => [
                'status' => $status ?: 'all',
                'template_id' => $templateId ?: 'all',
                'year' => $year ?: 'all',
                'plant' => $plant ?: 'all',
                'area' => $area ?: 'all',
                'search' => $search,
            ],
        ];
    }
}
