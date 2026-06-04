<?php

namespace App\Services;

use App\Models\MasterDataRecord;
use App\Models\MasterDataStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MasterDataStatusService
{
    public const SOURCE_BULK_FILTERED = 'bulk_filtered';

    public const SOURCE_BULK_SELECTED = 'bulk_selected';

    public const SOURCE_DIGITAL_FORM = 'digital_form';

    public const SOURCE_MANUAL_ADMIN = 'manual_admin';

    public const SOURCE_SUBMISSION_DELETED = 'submission_deleted';

    public function setStatus(
        MasterDataRecord $record,
        string $status,
        string $source,
        ?User $user = null,
        ?Model $submission = null
    ): MasterDataRecord {
        $previousStatus = $record->status;

        if ($previousStatus === $status) {
            return $record;
        }

        $record->forceFill(['status' => $status])->save();
        $record->refresh();

        MasterDataStatusHistory::create([
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

        return $record;
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
