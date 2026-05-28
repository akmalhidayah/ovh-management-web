<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserTopbarNotifications
{
    public static function make(string $role, int $limit = 6): array
    {
        if (! in_array($role, ['qc', 'commissioning'], true)) {
            return ['count' => 0, 'items' => collect()];
        }

        $records = self::availableRecords($role);

        return [
            'count' => $records->count(),
            'items' => $records
                ->take($limit)
                ->map(fn (MasterDataRecord $record) => self::item($record, $role))
                ->values(),
        ];
    }

    private static function availableRecords(string $role): Collection
    {
        $category = $role === 'qc'
            ? MasterDataRecord::CATEGORY_QC
            : MasterDataRecord::CATEGORY_COMMISSIONING;

        $submissions = ($role === 'qc' ? QcFormSubmission::query() : CommissioningFormSubmission::query())
            ->latest('submitted_at')
            ->latest()
            ->get();

        return MasterDataRecord::query()
            ->where('document_category', $category)
            ->where('status', 'active')
            ->where(fn ($query) => $query
                ->whereNull('inspection_status')
                ->orWhere('inspection_status', ''))
            ->when(request('equipment_plant'), fn ($query, $plant) => $query->where('plant', $plant))
            ->when(request('equipment_area'), fn ($query, $area) => $query->where('area', $area))
            ->when(request('equipment_status') && request('equipment_status') !== 'not_started', fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('updated_at')
            ->orderBy('area')
            ->orderBy('section_no')
            ->get()
            ->reject(fn (MasterDataRecord $record) => $submissions->contains(
                fn (Model $submission) => self::submissionMatchesMasterRecord($submission, $record)
            ))
            ->values();
    }

    private static function item(MasterDataRecord $record, string $role): array
    {
        $route = $role === 'qc'
            ? 'user.qc.forms.create'
            : 'user.commissioning.forms.create';

        return [
            'type' => $role === 'qc' ? 'QC' : 'Commissioning',
            'title' => Str::limit($record->description ?: 'Equipment baru', 44),
            'meta' => Str::limit(collect([$record->section_no, $record->area, $record->plant])->filter()->implode(' / '), 64),
            'url' => route($route, [
                'master_data_record_id' => $record->id,
                'area' => $record->area,
            ]),
        ];
    }

    private static function submissionMatchesMasterRecord(Model $submission, MasterDataRecord $record): bool
    {
        $header = $submission instanceof QcFormSubmission
            ? ($submission->general_info ?? [])
            : ($submission->header_data ?? []);
        $functionalLocation = $submission instanceof QcFormSubmission
            ? ($header['functional_location'] ?? null)
            : $submission->functional_location;
        $equipmentNo = $submission instanceof QcFormSubmission
            ? ($header['id_equipment'] ?? null)
            : $submission->equipment_no;

        return (filled($header['master_data_record_id'] ?? null) && (string) $header['master_data_record_id'] === (string) $record->id)
            || (filled($functionalLocation) && (string) $functionalLocation === (string) $record->func_location)
            || (filled($equipmentNo) && filled($record->equipment_no) && (string) $equipmentNo === (string) $record->equipment_no);
    }
}
