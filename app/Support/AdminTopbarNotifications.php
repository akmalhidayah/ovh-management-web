<?php

namespace App\Support;

use App\Models\AdminSubmissionNotificationRead;
use App\Models\CommissioningFormSubmission;
use App\Models\QcFormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminTopbarNotifications
{
    public static function make(int $limit = 6): array
    {
        $readKeys = self::readKeys();
        $items = self::notificationItems()
            ->map(function (array $item) use ($readKeys): array {
                $item['is_read'] = $readKeys->contains($item['notification_key']);

                return $item;
            })
            ->values();
        $unreadItems = $items
            ->reject(fn (array $item) => (bool) ($item['is_read'] ?? false))
            ->values();

        return [
            'count' => $unreadItems->count(),
            'total' => $items->count(),
            'items' => $unreadItems->take($limit)->values(),
        ];
    }

    public static function notificationStatuses(): array
    {
        return ['draft', 'submitted', 'pending_approval'];
    }

    public static function availableSubmissions(): Collection
    {
        return self::notificationItems()
            ->pluck('submission')
            ->values();
    }

    public static function notificationKey(Model $submission): string
    {
        return implode(':', [
            $submission::class,
            $submission->getKey(),
            $submission->status,
            self::eventTimestamp($submission),
        ]);
    }

    private static function notificationItems(): Collection
    {
        $qc = QcFormSubmission::query()
            ->whereIn('status', self::notificationStatuses())
            ->with('user')
            ->latest('updated_at')
            ->get()
            ->map(fn (QcFormSubmission $submission) => self::qcItem($submission));

        $commissioning = CommissioningFormSubmission::query()
            ->whereIn('status', self::notificationStatuses())
            ->with('user')
            ->latest('updated_at')
            ->get()
            ->map(fn (CommissioningFormSubmission $submission) => self::commissioningItem($submission));

        return $qc
            ->concat($commissioning)
            ->sortByDesc(fn (array $item) => $item['notified_at']?->timestamp ?? 0)
            ->values();
    }

    private static function qcItem(QcFormSubmission $submission): array
    {
        return [
            'type' => 'QC',
            'title' => Str::limit($submission->form_number ?: 'Form QC', 42),
            'description' => self::submittedByText($submission->user?->name, 'QC', $submission->status, self::qcAutoActivated($submission)),
            'meta' => Str::limit(collect([$submission->equipment, $submission->area])->filter()->implode(' / '), 58),
            'notified_at' => $submission->submitted_at ?: $submission->updated_at,
            'url' => route('admin.notifications.qc.open', $submission),
            'notification_key' => self::notificationKey($submission),
            'submission' => $submission,
        ];
    }

    private static function commissioningItem(CommissioningFormSubmission $submission): array
    {
        $header = $submission->header_data ?? [];

        return [
            'type' => 'CM',
            'title' => Str::limit($submission->form_number ?: 'Form Commissioning', 42),
            'description' => self::submittedByText($submission->user?->name, 'Commissioning', $submission->status, self::commissioningAutoActivated($submission)),
            'meta' => Str::limit(collect([$submission->equipment, $header['area'] ?? $submission->area])->filter()->implode(' / '), 58),
            'notified_at' => $submission->submitted_at ?: $submission->updated_at,
            'url' => route('admin.notifications.commissioning.open', $submission),
            'notification_key' => self::notificationKey($submission),
            'submission' => $submission,
        ];
    }

    private static function submittedByText(?string $name, string $type, string $status, bool $autoActivated): string
    {
        $actor = filled($name) ? $name : 'User';
        $action = $status === 'draft' ? 'menyimpan draft' : 'membuat form';
        $message = "{$actor} sudah {$action} {$type}.";

        if ($autoActivated) {
            $message .= ' Equipment otomatis diaktifkan.';
        }

        return Str::limit($message, 96);
    }

    private static function qcAutoActivated(QcFormSubmission $submission): bool
    {
        return (bool) data_get($submission->general_info ?? [], 'master_data_auto_activated');
    }

    private static function commissioningAutoActivated(CommissioningFormSubmission $submission): bool
    {
        return (bool) data_get($submission->header_data ?? [], 'master_data_auto_activated');
    }

    private static function readKeys(): Collection
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        return AdminSubmissionNotificationRead::query()
            ->where('user_id', $userId)
            ->pluck('notification_key');
    }

    private static function eventTimestamp(Model $submission): string
    {
        return (string) (optional($submission->updated_at)->getTimestamp()
            ?? optional($submission->submitted_at)->getTimestamp()
            ?? $submission->getKey());
    }
}
