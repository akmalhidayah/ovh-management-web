<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Qc\FormController as UserQcFormController;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Services\ApprovalFlowService;
use App\Services\InspectionSubmissionDeletionService;
use App\Services\MasterDataInspectionStatusService;
use App\Support\AdminMenuPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class QcSubmissionController extends Controller
{
    private const ERROR_PDF = 'ADMIN-QC-SUB-PDF-FAILED';
    private const ERROR_DESTROY = 'ADMIN-QC-SUB-DESTROY-FAILED';
    private const ERROR_RESTORE_DRAFT = 'ADMIN-QC-SUB-RESTORE-DRAFT-FAILED';

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

    public function restoreDraft(QcFormSubmission $submission): RedirectResponse
    {
        abort_if(auth()->user()?->role === AdminMenuPermissions::ROLE_APPROVAL, 403);

        if ($submission->status === 'draft') {
            return back()->with('success', 'Submission QC sudah berstatus draft.');
        }

        try {
            DB::transaction(function () use ($submission): void {
                app(ApprovalFlowService::class)->cancelFlow($submission, 'Submission restored to draft by admin');
                $generalInfo = $submission->general_info ?? [];
                $generalInfo['admin_restored_to_draft_at'] = now()->toISOString();
                $generalInfo['admin_restored_to_draft_by'] = auth()->id();
                $generalInfo['admin_restored_to_draft_by_name'] = auth()->user()?->name;

                $submission->forceFill([
                    'status' => 'draft',
                    'submitted_at' => null,
                    'general_info' => $generalInfo,
                ])->save();

                $this->syncMasterInspectionStatus($submission);
            });
        } catch (Throwable $exception) {
            Log::error(self::ERROR_RESTORE_DRAFT, [
                'actor_id' => auth()->id(),
                'controller' => self::class,
                'submission_id' => $submission->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['submission' => 'Submission QC gagal dikembalikan ke draft. Kode error: '.self::ERROR_RESTORE_DRAFT]);
        }

        Log::info('admin_qc_submission_restored_to_draft', [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'submission_id' => $submission->id,
        ]);

        return back()->with('success', 'Submission QC berhasil dikembalikan ke draft.');
    }

    public function destroy(QcFormSubmission $submission, InspectionSubmissionDeletionService $deletionService): RedirectResponse
    {
        abort_if(auth()->user()?->role === AdminMenuPermissions::ROLE_APPROVAL, 403);

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

    private function syncMasterInspectionStatus(QcFormSubmission $submission): void
    {
        $generalInfo = $submission->general_info ?? [];
        $query = MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_QC);

        $record = null;

        if (filled($generalInfo['master_data_record_id'] ?? null)) {
            $record = (clone $query)->whereKey($generalInfo['master_data_record_id'])->first();
        } elseif (filled($generalInfo['functional_location'] ?? null)) {
            $record = (clone $query)->where('func_location', $generalInfo['functional_location'])->first();
        } elseif (filled($generalInfo['id_equipment'] ?? null)) {
            $record = (clone $query)->where('equipment_no', $generalInfo['id_equipment'])->first();
        }

        if (! $record) {
            return;
        }

        app(MasterDataInspectionStatusService::class)->setStatus(
            $record,
            $this->qcInspectionStatusForRecord($record),
            MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
            auth()->user(),
            $submission
        );
    }

    private function qcInspectionStatusForRecord(MasterDataRecord $record): string
    {
        $submissions = QcFormSubmission::query()
            ->get()
            ->filter(fn (QcFormSubmission $submission) => $this->qcSubmissionMatchesRecord($submission, $record));

        if ($submissions->contains(fn (QcFormSubmission $submission) => ! in_array($submission->status, ['draft', 'revision', 'revision_required'], true))) {
            return 'close';
        }

        return 'ongoing';
    }

    private function qcSubmissionMatchesRecord(QcFormSubmission $submission, MasterDataRecord $record): bool
    {
        $generalInfo = $submission->general_info ?? [];

        return (filled($generalInfo['master_data_record_id'] ?? null) && (string) $generalInfo['master_data_record_id'] === (string) $record->id)
            || (filled($generalInfo['functional_location'] ?? null) && (string) $generalInfo['functional_location'] === (string) $record->func_location)
            || (filled($generalInfo['id_equipment'] ?? null) && filled($record->equipment_no) && (string) $generalInfo['id_equipment'] === (string) $record->equipment_no);
    }
}
