<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcFormSubmission;
use App\Support\UserRoleUiData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $selectedArea = $request->query('area', 'all');
        $baseQuery = QcFormSubmission::query()
            ->where('user_id', auth()->id())
            ->submitted();

        $areaOptions = (clone $baseQuery)
            ->whereNotNull('area')
            ->where('area', '<>', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $submissions = $baseQuery
            ->with('template')
            ->when($selectedArea !== 'all' && $selectedArea !== '', fn ($query) => $query->where('area', $selectedArea))
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('user.qc.history.index', array_merge(UserRoleUiData::qcHistory(), [
            'submissions' => $submissions,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
            'areaOptions' => $areaOptions,
            'selectedArea' => $selectedArea,
        ]));
    }
}
