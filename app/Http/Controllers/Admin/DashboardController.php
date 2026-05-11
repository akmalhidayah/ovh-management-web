<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Support\QcSubmissionPageData;
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
        $submissions = CommissioningFormSubmission::with(['template', 'user'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('form_number', 'like', "%{$search}%")
                        ->orWhere('equipment', 'like', "%{$search}%")
                        ->orWhere('area', 'like', "%{$search}%")
                        ->orWhere('functional_location', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.commissioning', [
            'submissions' => $submissions,
            'summary' => [
                'total' => CommissioningFormSubmission::count(),
                'submitted' => CommissioningFormSubmission::where('status', 'submitted')->count(),
                'draft' => CommissioningFormSubmission::where('status', 'draft')->count(),
            ],
        ]);
    }

    public function qc(Request $request): View
    {
        return view('admin.qc.index', QcSubmissionPageData::make($request, 'QC'));
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
