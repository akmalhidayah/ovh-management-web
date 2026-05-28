<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
use App\Models\QcFormSubmission;
use Illuminate\Support\Collection;

class AdminTopbarNotifications
{
    public static function make(int $limit = 6): array
    {
        $qc = QcFormSubmission::query()
            ->whereIn('status', ['submitted', 'pending_approval'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn (QcFormSubmission $submission) => self::qcItem($submission));

        $commissioning = CommissioningFormSubmission::query()
            ->whereIn('status', ['submitted', 'pending_approval'])
            ->whereNotNull('submitted_at')
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
            'title' => $submission->form_number ?: 'Form QC',
            'meta' => collect([$submission->equipment, $submission->area])->filter()->implode(' / '),
            'submitted_at' => $submission->submitted_at,
            'url' => route('admin.qc.submissions.pdf', $submission),
        ];
    }

    private static function commissioningItem(CommissioningFormSubmission $submission): array
    {
        $header = $submission->header_data ?? [];

        return [
            'type' => 'Commissioning',
            'title' => $submission->form_number ?: 'Form Commissioning',
            'meta' => collect([$submission->equipment, $header['area'] ?? $submission->area])->filter()->implode(' / '),
            'submitted_at' => $submission->submitted_at,
            'url' => route('admin.commissioning.submissions.pdf', $submission),
        ];
    }
}
