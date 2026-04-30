<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\UpdatesUserProfile;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use UpdatesUserProfile;

    public function show(): View
    {
        return view('user.qc.profile', UserRoleUiData::qcProfile());
    }
}
