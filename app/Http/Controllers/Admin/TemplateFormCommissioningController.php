<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TemplateFormCommissioningController extends Controller
{
    public function index(): View
    {
        return view('admin.template-form-commissioning.index');
    }
}
