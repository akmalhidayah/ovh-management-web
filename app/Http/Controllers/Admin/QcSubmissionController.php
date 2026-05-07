<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Qc\FormController as UserQcFormController;
use App\Models\QcFormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QcSubmissionController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.qc', $request->query());
    }

    public function pdf(QcFormSubmission $submission)
    {
        $submission->load(['template.blocks', 'rows', 'attachments', 'user']);

        return UserQcFormController::streamPdf($submission);
    }
}
