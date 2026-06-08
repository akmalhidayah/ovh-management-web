<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataInspectionStatusHistory;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionSubmissionDeletionService
{
    public function deleteQcPermanently(QcFormSubmission $submission): void
    {
        DB::transaction(function () use ($submission): void {
            $submission->loadMissing(['attachments', 'rows']);

            $this->resetQcMasterStatus($submission, auth()->user());
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
            $submission->forceDelete();
        });
    }

    public function resetQcMasterStatus(QcFormSubmission $submission, ?User $actor = null): void
    {
        $record = $this->qcMasterRecordForSubmission($submission);

        if (! $record) {
            return;
        }

        $remainingStatus = $this->remainingQcInspectionStatus($record, $submission);

        if ($this->submissionChangedMasterDataInspectionStatus($record, $submission)) {
            app(MasterDataInspectionStatusService::class)->setStatus(
                $record,
                $remainingStatus,
                MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
                $actor,
                $submission
            );
        }

        if ($remainingStatus === null) {
            $this->restoreAutoActivatedMasterStatus(
                $record,
                $submission->general_info ?? [],
                $submission,
                $actor
            );
        }
    }

    public function resetCommissioningMasterStatus(CommissioningFormSubmission $submission, ?User $actor = null): void
    {
        $record = $this->commissioningMasterRecordForSubmission($submission);

        if (! $record) {
            return;
        }

        if ($this->submissionChangedMasterDataInspectionStatus($record, $submission)) {
            app(MasterDataInspectionStatusService::class)->setStatus(
                $record,
                null,
                MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM,
                $actor,
                $submission
            );
        }

        $this->restoreAutoActivatedMasterStatus(
            $record,
            $submission->header_data ?? [],
            $submission,
            $actor
        );
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

    private function restoreAutoActivatedMasterStatus(
        MasterDataRecord $record,
        array $metadata,
        Model $submission,
        ?User $actor
    ): void {
        if (! (bool) ($metadata['master_data_auto_activated'] ?? false)) {
            return;
        }

        $previousStatus = $metadata['master_data_previous_status'] ?? null;
        app(MasterDataStatusService::class)->setStatus(
            $record,
            filled($previousStatus) ? $previousStatus : 'inactive',
            MasterDataStatusService::SOURCE_SUBMISSION_DELETED,
            $actor,
            $submission
        );
    }

    private function submissionChangedMasterDataInspectionStatus(MasterDataRecord $record, Model $submission): bool
    {
        return MasterDataInspectionStatusHistory::query()
            ->where('master_data_record_id', $record->id)
            ->where('source', MasterDataInspectionStatusService::SOURCE_DIGITAL_FORM)
            ->where('submission_type', $submission->getMorphClass())
            ->where('submission_id', $submission->getKey())
            ->exists();
    }

    private function remainingQcInspectionStatus(MasterDataRecord $record, QcFormSubmission $excludedSubmission): ?string
    {
        $remainingSubmissions = $this->remainingQcSubmissionsForMasterRecord($record, $excludedSubmission);

        if ($remainingSubmissions->isEmpty()) {
            return null;
        }

        if ($remainingSubmissions->contains(fn (QcFormSubmission $submission) => ! $this->isOngoingQcStatus($submission->status))) {
            return 'close';
        }

        return 'ongoing';
    }

    private function remainingQcSubmissionsForMasterRecord(MasterDataRecord $record, QcFormSubmission $excludedSubmission): Collection
    {
        return QcFormSubmission::query()
            ->whereKeyNot($excludedSubmission->getKey())
            ->get()
            ->filter(fn (QcFormSubmission $submission) => $this->qcSubmissionMatchesMasterRecord($submission, $record))
            ->values();
    }

    private function qcSubmissionMatchesMasterRecord(QcFormSubmission $submission, MasterDataRecord $record): bool
    {
        $header = $submission->general_info ?? [];

        return (filled($header['master_data_record_id'] ?? null) && (string) $header['master_data_record_id'] === (string) $record->id)
            || (filled($header['functional_location'] ?? null) && (string) $header['functional_location'] === (string) $record->func_location)
            || (filled($header['id_equipment'] ?? null) && filled($record->equipment_no) && (string) $header['id_equipment'] === (string) $record->equipment_no);
    }

    private function isOngoingQcStatus(?string $status): bool
    {
        return in_array($status, ['draft', 'revision', 'revision_required'], true);
    }

    private function qcMasterRecordForSubmission(QcFormSubmission $submission): ?MasterDataRecord
    {
        $header = $submission->general_info ?? [];
        $query = MasterDataRecord::query()
            ->where('document_category', MasterDataRecord::CATEGORY_QC);

        if (filled($header['master_data_record_id'] ?? null)) {
            return (clone $query)->whereKey($header['master_data_record_id'])->first();
        }

        if (filled($header['functional_location'] ?? null)) {
            return (clone $query)->where('func_location', $header['functional_location'])->first();
        }

        if (filled($header['id_equipment'] ?? null)) {
            return (clone $query)->where('equipment_no', $header['id_equipment'])->first();
        }

        return null;
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
