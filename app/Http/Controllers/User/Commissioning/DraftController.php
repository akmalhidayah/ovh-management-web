<?php

namespace App\Http\Controllers\User\Commissioning;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class DraftController extends Controller
{
    public function index(): View
    {
        return view('user.commissioning.drafts.index', UserRoleUiData::commissioningDrafts());
    }
}
