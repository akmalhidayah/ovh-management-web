<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Services\ApprovalFlowService;
use App\Services\InspectionSubmissionDeletionService;
use App\Services\MasterDataInspectionStatusService;
use App\Support\MasterDataIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommissioningSubmissionController extends Controller
{
    private const ERROR_DESTROY = 'ADMIN-COM-SUB-DESTROY-FAILED';
    private const ERROR_RESTORE_DRAFT = 'ADMIN-COM-SUB-RESTORE-DRAFT-FAILED';

    public function restoreDraft(CommissioningFormSubmission $submission): RedirectResponse
    {
        abort_if(auth()->user()?->isAdminApproval(), 403);

        if ($submission->status === 'draft') {
            return back()->with('success', 'Submission Commissioning sudah berstatus draft.');
        }

        try {
            DB::transaction(function () use ($submission): void {
                app(ApprovalFlowService::class)->cancelFlow($submission, 'Submission restored to draft by admin');
                $headerData = $submission->header_data ?? [];
                $headerData['admin_restored_to_draft_at'] = now()->toISOString();
                $headerData['admin_restored_to_draft_by'] = auth()->id();
                $headerData['admin_restored_to_draft_by_name'] = auth()->user()?->name;

                $submission->forceFill([
                    'status' => 'draft',
                    'submitted_at' => null,
                    'header_data' => $headerData,
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

            return back()->withErrors(['submission' => 'Submission Commissioning gagal dikembalikan ke draft. Kode error: '.self::ERROR_RESTORE_DRAFT]);
        }

        Log::info('admin_commissioning_submission_restored_to_draft', [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'submission_id' => $submission->id,
        ]);

        return back()->with('success', 'Submission Commissioning berhasil dikembalikan ke draft.');
    }

    public function destroy(
        CommissioningFormSubmission $submission,
        InspectionSubmissionDeletionService $deletionService
    ): RedirectResponse {
        abort_if(auth()->user()?->isAdminApproval(), 403);

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

    private function syncMasterInspectionStatus(CommissioningFormSubmission $submission): void
    {
        $header = $submission->header_data ?? [];
        $query = MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING);

        $record = null;

        if (filled($header['master_data_record_id'] ?? null)) {
            $record = (clone $query)->whereKey($header['master_data_record_id'])->first();
        } elseif (filled($submission->functional_location)) {
            $record = (clone $query)->where('func_location', $submission->functional_location)->first();
        } elseif ($equipmentNo = MasterDataIdentity::usableEquipmentNumber($submission->equipment_no)) {
            $record = (clone $query)->where('equipment_no', $equipmentNo)->first();
        }

        if (! $record) {
            return;
        }

        app(MasterDataInspectionStatusService::class)->setStatus(
            $record,
            $this->commissioningInspectionStatusForRecord($record),
            MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
            auth()->user(),
            $submission
        );
    }

    private function commissioningInspectionStatusForRecord(MasterDataRecord $record): string
    {
        $submissions = CommissioningFormSubmission::query()
            ->get()
            ->filter(fn (CommissioningFormSubmission $submission) => $this->commissioningSubmissionMatchesRecord($submission, $record));

        if ($submissions->contains(fn (CommissioningFormSubmission $submission) => ! in_array($submission->status, ['draft', 'revision_required'], true))) {
            return 'close';
        }

        return 'ongoing';
    }

    private function commissioningSubmissionMatchesRecord(CommissioningFormSubmission $submission, MasterDataRecord $record): bool
    {
        $header = $submission->header_data ?? [];

        return (filled($header['master_data_record_id'] ?? null) && (string) $header['master_data_record_id'] === (string) $record->id)
            || (filled($submission->functional_location) && (string) $submission->functional_location === (string) $record->func_location)
            || MasterDataIdentity::equipmentNumbersMatch($submission->equipment_no, $record->equipment_no);
    }
}
