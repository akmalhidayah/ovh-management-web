<?php

namespace App\Http\Controllers\User\Approval;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class PendingController extends Controller
{
    public function index(): View
    {
        return view('user.approval.pending.index', UserRoleUiData::approvalPending());
    }
}
