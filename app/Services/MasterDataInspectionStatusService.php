<?php

namespace App\Services;

use App\Models\MasterDataInspectionStatusHistory;
use App\Models\MasterDataRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MasterDataInspectionStatusService
{
    public const SOURCE_DIGITAL_FORM = 'digital_form';
    public const SOURCE_MANUAL_ADMIN = 'manual_admin';

    public function setStatus(
        MasterDataRecord $record,
        string $status,
        string $source,
        ?User $user = null,
        ?Model $submission = null
    ): MasterDataRecord {
        $previousStatus = $record->inspection_status;
        $shouldRecordHistory = $previousStatus !== $status || $this->shouldLogSubmission($record, $source, $submission);

        if ($previousStatus !== $status) {
            $record->forceFill(['inspection_status' => $status])->save();
        }

        if ($shouldRecordHistory) {
            $record->refresh();

            MasterDataInspectionStatusHistory::create([
                'master_data_record_id' => $record->id,
                'previous_status' => $previousStatus,
                'status' => $status,
                'source' => $source,
                'submission_type' => $submission ? $submission->getMorphClass() : null,
                'submission_id' => $submission?->getKey(),
                'changed_by' => $user?->id,
                'changed_at' => now(),
                'snapshot' => $this->snapshot($record, $submission),
            ]);
        }

        return $record;
    }

    private function shouldLogSubmission(MasterDataRecord $record, string $source, ?Model $submission): bool
    {
        if (! $submission) {
            return false;
        }

        return ! MasterDataInspectionStatusHistory::query()
            ->where('master_data_record_id', $record->id)
            ->where('source', $source)
            ->where('submission_type', $submission->getMorphClass())
            ->where('submission_id', $submission->getKey())
            ->exists();
    }

    private function snapshot(MasterDataRecord $record, ?Model $submission): array
    {
        return [
            'master_data' => [
                'id' => $record->id,
                'document_category' => $record->document_category,
                'year' => $record->year,
                'func_location' => $record->func_location,
                'equipment_no' => $record->equipment_no,
                'section_no' => $record->section_no,
                'description' => $record->description,
                'plant' => $record->plant,
                'area' => $record->area,
                'status' => $record->status,
                'inspection_status' => $record->inspection_status,
            ],
            'submission' => $submission ? [
                'id' => $submission->getKey(),
                'type' => $submission->getMorphClass(),
                'form_number' => $submission->getAttribute('form_number'),
                'status' => $submission->getAttribute('status'),
                'submitted_at' => optional($submission->getAttribute('submitted_at'))->toISOString(),
            ] : null,
        ];
    }
}
