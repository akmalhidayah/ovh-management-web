<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Models\QcFormSubmission;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class DraftController extends Controller
{
    public function index(): View
    {
        $submissions = QcFormSubmission::query()
            ->with('template')
            ->where('user_id', auth()->id())
            ->draft()
            ->latest()
            ->paginate(10);

        return view('user.qc.drafts.index', array_merge(UserRoleUiData::qcDrafts(), [
            'submissions' => $submissions,
            'statusLabels' => \App\Http\Controllers\User\Qc\FormController::statusLabels(),
        ]));
    }
}
