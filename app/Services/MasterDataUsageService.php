<?php

namespace App\Services;

use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use Illuminate\Support\Collection;

class MasterDataUsageService
{
    private const ACTIVE_SUBMISSION_STATUSES = [
        'draft',
        'submitted',
        'pending_approval',
        'approved',
        'revision',
        'revision_required',
    ];

    public function isInUse(MasterDataRecord $record): bool
    {
        return $this->partition(collect([$record]))['protected']->isNotEmpty();
    }

    public function partition(Collection $records): array
    {
        $usage = $this->usageKeys();
        [$protected, $eligible] = $records->partition(
            fn (MasterDataRecord $record) => $this->recordIsUsed($record, $usage)
        );

        return [
            'eligible' => $eligible->values(),
            'protected' => $protected->values(),
        ];
    }

    private function usageKeys(): array
    {
        $usage = [
            MasterDataRecord::CATEGORY_QC => $this->emptyUsageKeys(),
            MasterDataRecord::CATEGORY_COMMISSIONING => $this->emptyUsageKeys(),
        ];

        QcFormSubmission::query()
            ->whereIn('status', self::ACTIVE_SUBMISSION_STATUSES)
            ->get(['general_info'])
            ->each(function (QcFormSubmission $submission) use (&$usage): void {
                $generalInfo = $submission->general_info ?? [];

                $this->addUsageIdentity(
                    $usage[MasterDataRecord::CATEGORY_QC],
                    $generalInfo['master_data_record_id'] ?? null,
                    $generalInfo['functional_location'] ?? null,
                    $generalInfo['id_equipment'] ?? null
                );
            });

        CommissioningFormSubmission::query()
            ->whereIn('status', self::ACTIVE_SUBMISSION_STATUSES)
            ->get(['header_data', 'functional_location', 'equipment_no'])
            ->each(function (CommissioningFormSubmission $submission) use (&$usage): void {
                $header = $submission->header_data ?? [];

                $this->addUsageIdentity(
                    $usage[MasterDataRecord::CATEGORY_COMMISSIONING],
                    $header['master_data_record_id'] ?? null,
                    $submission->functional_location,
                    $submission->equipment_no
                );
            });

        return $usage;
    }

    private function emptyUsageKeys(): array
    {
        return [
            'ids' => [],
            'functional_locations' => [],
            'equipment_numbers' => [],
        ];
    }

    private function addUsageIdentity(
        array &$usage,
        mixed $recordId,
        mixed $functionalLocation,
        mixed $equipmentNumber
    ): void {
        if (filled($recordId)) {
            $usage['ids'][(string) (int) $recordId] = true;

            return;
        }

        $functionalLocation = $this->normalize($functionalLocation);
        if ($functionalLocation !== '') {
            $usage['functional_locations'][$functionalLocation] = true;

            return;
        }

        $equipmentNumber = $this->normalize($equipmentNumber);
        if ($equipmentNumber !== '') {
            $usage['equipment_numbers'][$equipmentNumber] = true;
        }
    }

    private function recordIsUsed(MasterDataRecord $record, array $usage): bool
    {
        $categoryUsage = $usage[$record->document_category] ?? $this->emptyUsageKeys();

        return isset($categoryUsage['ids'][(string) $record->id])
            || isset($categoryUsage['functional_locations'][$this->normalize($record->func_location)])
            || (
                filled($record->equipment_no)
                && isset($categoryUsage['equipment_numbers'][$this->normalize($record->equipment_no)])
            );
    }

    private function normalize(mixed $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
