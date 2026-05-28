<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataInspectionStatusHistory;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionSubmissionDeletionService
{
    public function deleteQcPermanently(QcFormSubmission $submission): void
    {
        DB::transaction(function () use ($submission): void {
            $submission->loadMissing(['attachments', 'rows']);

            $this->deleteStoredFiles($submission);
            $this->deleteApprovalFlows($submission);
            $submission->attachments()->delete();
            $submission->rows()->delete();
            $submission->forceDelete();
        });
    }

    public function deleteCommissioningPermanently(CommissioningFormSubmission $submission, ?User $actor = null): void
    {
        DB::transaction(function () use ($submission, $actor): void {
            $submission->loadMissing(['attachments']);

            $this->resetCommissioningMasterStatus($submission, $actor);
            $this->deleteStoredFiles($submission);
            $this->deleteApprovalFlows($submission);
            $submission->attachments()->delete();
            $submission->delete();
        });
    }

    private function deleteStoredFiles(Model $submission): void
    {
        foreach ($submission->getRelationValue('attachments') ?? [] as $attachment) {
            $path = trim((string) $attachment->file_path);

            if ($path === '') {
                continue;
            }

            Storage::disk('local')->delete($path);
            Storage::disk('public')->delete($path);
        }

        $this->approvalFlows($submission)
            ->with('steps')
            ->get()
            ->flatMap(fn (ApprovalFlow $flow) => $flow->steps)
            ->each(function ($step): void {
                $path = trim((string) $step->signature_path);

                if ($path !== '') {
                    Storage::disk('public')->delete($path);
                }
            });
    }

    private function deleteApprovalFlows(Model $submission): void
    {
        $this->approvalFlows($submission)
            ->with('steps.links', 'events')
            ->get()
            ->each(function (ApprovalFlow $flow): void {
                $flow->events()->delete();
                $flow->delete();
            });
    }

    private function approvalFlows(Model $submission)
    {
        return ApprovalFlow::query()
            ->where('approvable_type', $submission->getMorphClass())
            ->where('approvable_id', $submission->getKey());
    }

    private function resetCommissioningMasterStatus(CommissioningFormSubmission $submission, ?User $actor): void
    {
        $record = $this->commissioningMasterRecordForSubmission($submission);

        if (! $record || ! $this->submissionChangedMasterDataInspectionStatus($record, $submission)) {
            return;
        }

        app(MasterDataInspectionStatusService::class)->setStatus(
            $record,
            null,
            MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
            $actor,
            $submission
        );
    }

    private function submissionChangedMasterDataInspectionStatus(MasterDataRecord $record, CommissioningFormSubmission $submission): bool
    {
        return MasterDataInspectionStatusHistory::query()
            ->where('master_data_record_id', $record->id)
            ->where('source', MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM)
            ->where('submission_type', $submission->getMorphClass())
            ->where('submission_id', $submission->getKey())
            ->exists();
    }

    private function commissioningMasterRecordForSubmission(CommissioningFormSubmission $submission): ?MasterDataRecord
    {
        $header = $submission->header_data ?? [];
        $query = MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_COMMISSIONING);

        if (filled($header['master_data_record_id'] ?? null)) {
            return (clone $query)->whereKey($header['master_data_record_id'])->first();
        }

        if (filled($submission->functional_location)) {
            return (clone $query)->where('func_location', $submission->functional_location)->first();
        }

        if (filled($submission->equipment_no)) {
            return (clone $query)->where('equipment_no', $submission->equipment_no)->first();
        }

        return null;
    }
}
