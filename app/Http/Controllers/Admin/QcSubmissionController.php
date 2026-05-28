<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Qc\FormController as UserQcFormController;
use App\Models\QcFormSubmission;
use App\Services\InspectionSubmissionDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class QcSubmissionController extends Controller
{
    private const ERROR_PDF = 'ADMIN-QC-SUB-PDF-FAILED';
    private const ERROR_DESTROY = 'ADMIN-QC-SUB-DESTROY-FAILED';

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.qc', $request->query());
    }

    public function pdf(QcFormSubmission $submission)
    {
        $submission->load(['template.blocks', 'rows', 'attachments', 'user']);

        try {
            $response = UserQcFormController::streamPdf($submission);
        } catch (Throwable $exception) {
            Log::error(self::ERROR_PDF, [
                'actor_id' => auth()->id(),
                'controller' => self::class,
                'submission_id' => $submission->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['pdf' => 'PDF QC gagal dibuka. Kode error: '.self::ERROR_PDF]);
        }

        Log::info('admin_qc_submission_pdf_opened', [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'submission_id' => $submission->id,
            'status' => $submission->status,
        ]);

        return $response;
    }

    public function destroy(QcFormSubmission $submission, InspectionSubmissionDeletionService $deletionService): RedirectResponse
    {
        $submissionId = $submission->id;

        try {
            $deletionService->deleteQcPermanently($submission);
        } catch (Throwable $exception) {
            Log::error(self::ERROR_DESTROY, [
                'actor_id' => auth()->id(),
                'controller' => self::class,
                'submission_id' => $submissionId,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['submission' => 'Submission QC gagal dihapus permanen. Kode error: '.self::ERROR_DESTROY]);
        }

        Log::info('admin_qc_submission_permanently_deleted', [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'submission_id' => $submissionId,
        ]);

        return back()->with('success', 'Submission QC berhasil dihapus permanen.');
    }
}
