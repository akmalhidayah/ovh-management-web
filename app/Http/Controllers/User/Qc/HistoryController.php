<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcFormSubmission;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $submissions = QcFormSubmission::query()
            ->with('template')
            ->where('user_id', auth()->id())
            ->submitted()
            ->latest('submitted_at')
            ->paginate(10);

        return view('user.qc.history.index', array_merge(UserRoleUiData::qcHistory(), [
            'submissions' => $submissions,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
        ]));
    }
}
