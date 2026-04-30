<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('user.qc.dashboard', UserRoleUiData::qcDashboard());
    }
}
