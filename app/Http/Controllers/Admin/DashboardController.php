<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function commissioning(): View
    {
        return view('admin.commissioning');
    }

    public function qc(): View
    {
        return view('admin.qc');
    }

    public function equipment(): View
    {
        return view('admin.equipment');
    }

    public function mom(): View
    {
        return view('admin.mom');
    }

    public function dokument(): View
    {
        return view('admin.dokument');
    }

    public function masterData(): View
    {
        return view('admin.master-data');
    }
}
