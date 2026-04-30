<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        return view('user.qc.history.index', UserRoleUiData::qcHistory());
    }
}
