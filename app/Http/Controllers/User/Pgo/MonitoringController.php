<?php

namespace App\Http\Controllers\User\Pgo;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        return view('user.pgo.monitoring.index', UserRoleUiData::pgoMonitoring());
    }
}
