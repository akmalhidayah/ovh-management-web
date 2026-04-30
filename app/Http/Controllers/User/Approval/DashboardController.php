<?php

namespace App\Http\Controllers\User\Approval;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('user.approval.dashboard', UserRoleUiData::approvalDashboard());
    }
}
