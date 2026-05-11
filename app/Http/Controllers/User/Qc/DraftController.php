<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcFormSubmission;
use App\Support\UserRoleUiData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DraftController extends Controller
{
    public function index(Request $request): View
    {
        $selectedArea = $request->query('area', 'all');
        $baseQuery = QcFormSubmission::query()
            ->where('user_id', auth()->id())
            ->draft();

        $areaOptions = (clone $baseQuery)
            ->whereNotNull('area')
            ->where('area', '<>', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $submissions = $baseQuery
            ->with('template')
            ->when($selectedArea !== 'all' && $selectedArea !== '', fn ($query) => $query->where('area', $selectedArea))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('user.qc.drafts.index', array_merge(UserRoleUiData::qcDrafts(), [
            'submissions' => $submissions,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
            'areaOptions' => $areaOptions,
            'selectedArea' => $selectedArea,
        ]));
    }
}
