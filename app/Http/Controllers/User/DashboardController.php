<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        return view('user.dashboard');
    }

    public function overview(): View
    {
        return view('user.overview');
    }

    public function procurement(): View
    {
        return view('user.procurement');
    }

    public function kalenderOverhaul(): View
    {
        return view('user.kalender-overhaul');
    }

    public function schedule(): View
    {
        return view('user.schedule');
    }

    public function commissioning(): View
    {
        return view('user.commissioning');
    }

    public function qc(): View
    {
        return view('user.qc');
    }

    public function equipment(): View
    {
        return view('user.equipment');
    }

    public function mom(): View
    {
        return view('user.mom');
    }

    public function dokumen(): View
    {
        return view('user.dokumen');
    }
}
