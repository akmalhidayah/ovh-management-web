<?php

namespace App\Http\Controllers\User\Commissioning;

use App\Http\Controllers\Controller;
use App\Support\UserRoleUiData;
use Illuminate\View\View;

class FormController extends Controller
{
    public function create(): View
    {
        return view('user.commissioning.forms.create', UserRoleUiData::commissioningForm());
    }
}
