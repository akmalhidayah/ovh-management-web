<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\UserNotificationRead;
use App\Models\UserSubmissionNotificationRead;
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
        $readRecordIds = self::readRecordIds($role);
        $recordItems = collect($records
            ->map(fn (MasterDataRecord $record) => self::recordItem($record, $role, $readRecordIds->contains((int) $record->id)))
            ->all());
        $submissionItems = self::submissionItems($role);
        $items = $recordItems
            ->merge($submissionItems)
            ->sortByDesc('sort_at')
            ->values();
        $unreadRecordCount = $records
            ->reject(fn (MasterDataRecord $record) => $readRecordIds->contains((int) $record->id))
            ->count();
        $unreadSubmissionCount = $submissionItems
            ->reject(fn (array $item) => (bool) ($item['is_read'] ?? false))
            ->count();

        return [
            'count' => $unreadRecordCount + $unreadSubmissionCount,
            'total' => $items->count(),
            'items' => $items
                ->take($limit)
                ->values(),
        ];
    }

    public static function availableRecordIds(string $role): array
    {
        if (! in_array($role, ['qc', 'commissioning'], true)) {
            return [];
        }

        return self::availableRecords($role)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function availableSubmissionNotifications(string $role): Collection
    {
        if (! in_array($role, ['qc', 'commissioning'], true) || ! auth()->id()) {
            return collect();
        }

        return self::submissionQuery($role)
            ->where('user_id', auth()->id())
            ->whereIn('status', self::submissionNotificationStatuses())
            ->latest('updated_at')
            ->latest()
            ->limit(30)
            ->get()
            ->filter(fn (Model $submission) => self::submissionIsNotifiable($submission))
            ->values();
    }

    public static function submissionNotificationStatuses(): array
    {
        return ['approved', 'revision', 'revision_required', 'rejected', 'draft'];
    }

    public static function submissionNotificationKey(Model $submission): string
    {
        return implode(':', [
            $submission::class,
            $submission->getKey(),
            $submission->status,
            self::submissionEventTimestamp($submission),
        ]);
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
            ->when(self::preferredProfileAreas(), fn ($query, array $areas) => $query->whereIn('area', $areas))
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

    private static function recordItem(MasterDataRecord $record, string $role, bool $isRead): array
    {
        $route = $role === 'qc' ? 'user.qc.notifications.open' : 'user.commissioning.notifications.open';

        return [
            'type' => $role === 'qc' ? 'QC' : 'Commissioning',
            'title' => Str::limit($record->description ?: 'Equipment baru', 44),
            'description' => $role === 'qc'
                ? 'Equipment baru aktif untuk dibuat form QC.'
                : 'Equipment baru aktif untuk dibuat form Commissioning.',
            'meta' => Str::limit(collect([$record->section_no, $record->area, $record->plant])->filter()->implode(' / '), 64),
            'url' => route($route, $record),
            'is_read' => $isRead,
            'sort_at' => optional($record->updated_at)->timestamp ?? 0,
        ];
    }

    private static function submissionItems(string $role): Collection
    {
        $submissions = self::availableSubmissionNotifications($role);
        $readKeys = self::readSubmissionKeys($role);

        return $submissions
            ->map(fn (Model $submission) => self::submissionItem(
                $submission,
                $role,
                $readKeys->contains(self::submissionReadKey($submission))
            ));
    }

    private static function submissionItem(Model $submission, string $role, bool $isRead): array
    {
        $isApproved = $submission->status === 'approved';
        $isRestoredDraft = $submission->status === 'draft';
        $roleLabel = $role === 'qc' ? 'QC' : 'Commissioning';
        $statusLabel = $isApproved ? 'Disetujui' : ($isRestoredDraft ? 'Draft' : 'Ditolak');
        $formNumber = (string) ($submission->form_number ?: $submission->getKey());

        return [
            'type' => $roleLabel,
            'title' => self::submissionTitle($roleLabel, $submission->status),
            'description' => self::submissionDescription($formNumber, $submission->status),
            'meta' => Str::limit(collect([$formNumber, $submission->equipment, $statusLabel])->filter()->implode(' / '), 64),
            'url' => route("user.{$role}.notifications.submissions.open", $submission),
            'is_read' => $isRead,
            'sort_at' => optional($submission->updated_at)->timestamp ?? optional($submission->submitted_at)->timestamp ?? 0,
        ];
    }

    private static function readRecordIds(string $role): Collection
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        return UserNotificationRead::query()
            ->where('user_id', $userId)
            ->where('role', $role)
            ->pluck('master_data_record_id')
            ->map(fn ($id) => (int) $id);
    }

    private static function readSubmissionKeys(string $role): Collection
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        return UserSubmissionNotificationRead::query()
            ->where('user_id', $userId)
            ->where('role', $role)
            ->get(['notification_key'])
            ->pluck('notification_key');
    }

    private static function submissionReadKey(Model $submission): string
    {
        return self::submissionNotificationKey($submission);
    }

    private static function submissionQuery(string $role)
    {
        return $role === 'qc'
            ? QcFormSubmission::query()
            : CommissioningFormSubmission::query();
    }

    public static function submissionIsNotifiable(Model $submission): bool
    {
        if ($submission->status !== 'draft') {
            return in_array($submission->status, ['approved', 'revision', 'revision_required', 'rejected'], true);
        }

        return filled(self::adminRestoredToDraftAt($submission));
    }

    private static function submissionTitle(string $roleLabel, string $status): string
    {
        return match ($status) {
            'approved' => "Submission {$roleLabel} disetujui",
            'draft' => "Submission {$roleLabel} dikembalikan ke draft",
            default => "Submission {$roleLabel} ditolak",
        };
    }

    private static function submissionDescription(string $formNumber, string $status): string
    {
        return match ($status) {
            'approved' => "Form {$formNumber} sudah disetujui final.",
            'draft' => "Form {$formNumber} dikembalikan admin ke draft dan bisa diedit lagi.",
            default => "Form {$formNumber} ditolak approval dan perlu diperbaiki.",
        };
    }

    private static function adminRestoredToDraftAt(Model $submission): ?string
    {
        $data = $submission instanceof QcFormSubmission
            ? ($submission->general_info ?? [])
            : ($submission->header_data ?? []);

        return $data['admin_restored_to_draft_at'] ?? null;
    }

    private static function submissionEventTimestamp(Model $submission): string
    {
        if ($submission->status === 'draft') {
            return (string) self::adminRestoredToDraftAt($submission);
        }

        return (string) (optional($submission->updated_at)->getTimestamp() ?? optional($submission->submitted_at)->getTimestamp() ?? $submission->getKey());
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
            || MasterDataIdentity::equipmentNumbersMatch($equipmentNo, $record->equipment_no);
    }

    private static function preferredProfileAreas(): array
    {
        return collect(auth()->user()?->profile_areas ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
