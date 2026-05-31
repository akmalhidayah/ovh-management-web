<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
use App\Models\QcFormSubmission;
use Illuminate\Support\Str;

class AdminTopbarNotifications
{
    public static function make(int $limit = 6): array
    {
        $qc = QcFormSubmission::query()
            ->whereIn('status', ['draft', 'submitted', 'pending_approval'])
            ->with('user')
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (QcFormSubmission $submission) => self::qcItem($submission));

        $commissioning = CommissioningFormSubmission::query()
            ->whereIn('status', ['draft', 'submitted', 'pending_approval'])
            ->with('user')
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (CommissioningFormSubmission $submission) => self::commissioningItem($submission));

        $items = $qc
            ->concat($commissioning)
            ->sortByDesc(fn (array $item) => $item['notified_at']?->timestamp ?? 0)
            ->take($limit)
            ->values();

        return [
            'count' => self::countPending(),
            'items' => $items,
        ];
    }

    private static function countPending(): int
    {
        return QcFormSubmission::query()
            ->whereIn('status', ['draft', 'submitted', 'pending_approval'])
            ->count()
            + CommissioningFormSubmission::query()
                ->whereIn('status', ['draft', 'submitted', 'pending_approval'])
                ->count();
    }

    private static function qcItem(QcFormSubmission $submission): array
    {
        return [
            'type' => 'QC',
            'title' => Str::limit($submission->form_number ?: 'Form QC', 42),
            'description' => self::submittedByText($submission->user?->name, 'QC', $submission->status, self::qcAutoActivated($submission)),
            'meta' => Str::limit(collect([$submission->equipment, $submission->area])->filter()->implode(' / '), 58),
            'notified_at' => $submission->submitted_at ?: $submission->updated_at,
            'url' => route('admin.qc.submissions.pdf', $submission),
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
            'url' => route('admin.commissioning.submissions.pdf', $submission),
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
}
