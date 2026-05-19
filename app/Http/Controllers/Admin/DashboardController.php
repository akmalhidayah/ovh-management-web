<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminInspectionSubmissionPageData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function overview(): View
    {
        return view('admin.overview');
    }

    public function procurement(): View
    {
        return view('admin.procurement');
    }

    public function kalenderOverhaul(): View
    {
        return view('admin.kalender-overhaul');
    }

    public function schedule(): View
    {
        return view('admin.schedule');
    }

    public function commissioning(Request $request): View
    {
        $request->merge(['type' => 'commissioning']);

        return view('admin.qc.index', AdminInspectionSubmissionPageData::make($request, 'commissioning'));
    }

    public function qc(Request $request): View
    {
        $request->merge(['type' => 'qc']);

        return view('admin.qc.index', AdminInspectionSubmissionPageData::make($request, 'qc'));
    }

    public function equipment(): View
    {
        return view('admin.equipment');
    }

    public function mom(): View
    {
        return view('admin.mom');
    }

    public function dokumen(): View
    {
        return view('admin.dokumen');
    }

}
