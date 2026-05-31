<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Services\InspectionSubmissionDeletionService;
use App\Support\AdminMenuPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommissioningSubmissionController extends Controller
{
    private const ERROR_DESTROY = 'ADMIN-COM-SUB-DESTROY-FAILED';

    public function destroy(
        CommissioningFormSubmission $submission,
        InspectionSubmissionDeletionService $deletionService
    ): RedirectResponse {
        abort_if(auth()->user()?->role === AdminMenuPermissions::ROLE_APPROVAL, 403);

        $submissionId = $submission->id;

        try {
            $deletionService->deleteCommissioningPermanently($submission, auth()->user());
        } catch (Throwable $exception) {
            Log::error(self::ERROR_DESTROY, [
                'actor_id' => auth()->id(),
                'controller' => self::class,
                'submission_id' => $submissionId,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['submission' => 'Submission Commissioning gagal dihapus permanen. Kode error: '.self::ERROR_DESTROY]);
        }

        Log::info('admin_commissioning_submission_permanently_deleted', [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'submission_id' => $submissionId,
        ]);

        return back()->with('success', 'Submission Commissioning berhasil dihapus permanen.');
    }
}
