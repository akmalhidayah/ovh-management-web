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
            ->whereIn('status', ['submitted', 'pending_approval', 'approved', 'revision_required', 'rejected', 'cancelled']);

        $areaOptions = (clone $baseQuery)
            ->whereNotNull('area')
            ->where('area', '<>', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $submissions = $baseQuery
                ->with(['template', 'approvalFlow.steps'])
                ->when($selectedArea !== 'all' && $selectedArea !== '', fn ($query) => $query->where('area', $selectedArea))
                ->latest('submitted_at')
                ->paginate(15)
                ->withQueryString();

        return view('user.commissioning.history.index', array_merge(UserRoleUiData::commissioningHistory(), [
            'submissions' => $submissions,
            'areaOptions' => $areaOptions,
            'selectedArea' => $selectedArea,
            'statusLabels' => [
                'submitted' => 'Menunggu Review',
                'pending_approval' => 'Menunggu Approval',
                'approved' => 'Disetujui',
                'revision_required' => 'Perlu Revisi',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
            ],
        ]));
    }
}
