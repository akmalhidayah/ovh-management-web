<?php

namespace App\Http\Controllers\User\Qc;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class FormController extends Controller
{
    public function create(): View
    {
        return view('user.qc.forms.create', UserRoleUiData::qcForm());
    }
}
