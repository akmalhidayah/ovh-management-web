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
            ->whereIn('status', ['submitted', 'pending_approval'])
            ->whereNotNull('submitted_at')
            ->with('user')
            ->latest('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn (QcFormSubmission $submission) => self::qcItem($submission));

        $commissioning = CommissioningFormSubmission::query()
            ->whereIn('status', ['submitted', 'pending_approval'])
            ->whereNotNull('submitted_at')
            ->with('user')
            ->latest('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn (CommissioningFormSubmission $submission) => self::commissioningItem($submission));

        $items = $qc
            ->concat($commissioning)
            ->sortByDesc(fn (array $item) => $item['submitted_at']?->timestamp ?? 0)
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
            ->whereIn('status', ['submitted', 'pending_approval'])
            ->whereNotNull('submitted_at')
            ->count()
            + CommissioningFormSubmission::query()
                ->whereIn('status', ['submitted', 'pending_approval'])
                ->whereNotNull('submitted_at')
                ->count();
    }

    private static function qcItem(QcFormSubmission $submission): array
    {
        return [
            'type' => 'QC',
            'title' => Str::limit($submission->form_number ?: 'Form QC', 42),
            'description' => self::submittedByText($submission->user?->name, 'QC'),
            'meta' => Str::limit(collect([$submission->equipment, $submission->area])->filter()->implode(' / '), 58),
            'submitted_at' => $submission->submitted_at,
            'url' => route('admin.qc.submissions.pdf', $submission),
        ];
    }

    private static function commissioningItem(CommissioningFormSubmission $submission): array
    {
        $header = $submission->header_data ?? [];

        return [
            'type' => 'Commissioning',
            'title' => Str::limit($submission->form_number ?: 'Form Commissioning', 42),
            'description' => self::submittedByText($submission->user?->name, 'Commissioning'),
            'meta' => Str::limit(collect([$submission->equipment, $header['area'] ?? $submission->area])->filter()->implode(' / '), 58),
            'submitted_at' => $submission->submitted_at,
            'url' => route('admin.commissioning.submissions.pdf', $submission),
        ];
    }

    private static function submittedByText(?string $name, string $type): string
    {
        $actor = filled($name) ? $name : 'User';

        return Str::limit("{$actor} sudah membuat form {$type}.", 72);
    }
}
