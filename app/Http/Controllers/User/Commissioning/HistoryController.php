<?php

namespace App\Http\Controllers\User\Commissioning;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Support\UserRoleUiData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $selectedArea = $request->query('area', 'all');
        $baseQuery = CommissioningFormSubmission::query()
            ->where('user_id', auth()->id())
            ->where('status', 'submitted');

        $areaOptions = (clone $baseQuery)
            ->whereNotNull('area')
            ->where('area', '<>', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        return view('user.commissioning.history.index', array_merge(UserRoleUiData::commissioningHistory(), [
            'submissions' => $baseQuery
                ->with('template')
                ->when($selectedArea !== 'all' && $selectedArea !== '', fn ($query) => $query->where('area', $selectedArea))
                ->latest('submitted_at')
                ->paginate(15)
                ->withQueryString(),
            'areaOptions' => $areaOptions,
            'selectedArea' => $selectedArea,
        ]));
    }
}
