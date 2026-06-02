<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSubmissionNotificationRead;
use App\Models\CommissioningFormSubmission;
use App\Models\QcFormSubmission;
use App\Support\AdminTopbarNotifications;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function readAll(Request $request): RedirectResponse
    {
        foreach (AdminTopbarNotifications::availableSubmissions() as $submission) {
            $this->markSubmissionRead($request, $submission);
        }

        return back()->with('status', 'Notifikasi admin ditandai sudah dibaca.');
    }

    public function openQc(Request $request, QcFormSubmission $submission): RedirectResponse
    {
        abort_unless($this->isNotifiable($submission), 404);

        $this->markSubmissionRead($request, $submission);

        return redirect()->route('admin.qc.submissions.pdf', $submission);
    }

    public function openCommissioning(Request $request, CommissioningFormSubmission $submission): RedirectResponse
    {
        abort_unless($this->isNotifiable($submission), 404);

        $this->markSubmissionRead($request, $submission);

        return redirect()->route('admin.commissioning.submissions.pdf', $submission);
    }

    private function markSubmissionRead(Request $request, Model $submission): void
    {
        AdminSubmissionNotificationRead::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'submission_type' => $submission::class,
                'submission_id' => $submission->getKey(),
                'status' => $submission->status,
                'notification_key' => AdminTopbarNotifications::notificationKey($submission),
            ],
            ['read_at' => now()]
        );
    }

    private function isNotifiable(Model $submission): bool
    {
        return in_array($submission->status, AdminTopbarNotifications::notificationStatuses(), true);
    }
}
