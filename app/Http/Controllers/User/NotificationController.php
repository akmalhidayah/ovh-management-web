<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Models\UserNotificationRead;
use App\Models\UserSubmissionNotificationRead;
use App\Support\UserTopbarNotifications;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function readAll(Request $request): RedirectResponse
    {
        $role = $this->roleFromRoute($request);

        foreach (UserTopbarNotifications::availableRecordIds($role) as $recordId) {
            $this->markRead($request, $role, $recordId);
        }

        foreach (UserTopbarNotifications::availableSubmissionNotifications($role) as $submission) {
            $this->markSubmissionRead($request, $role, $submission);
        }

        return back()->with('status', 'Notifikasi ditandai sudah dibaca.');
    }

    public function open(Request $request, MasterDataRecord $masterDataRecord): RedirectResponse
    {
        $role = $this->roleFromRoute($request);
        abort_unless($this->recordMatchesRole($masterDataRecord, $role), 404);

        $this->markRead($request, $role, $masterDataRecord->id);

        $route = $role === 'qc'
            ? 'user.qc.forms.create'
            : 'user.commissioning.forms.create';

        return redirect()->route($route, [
            'master_data_record_id' => $masterDataRecord->id,
            'area' => $masterDataRecord->area,
        ]);
    }

    public function openSubmission(Request $request, string $submission): RedirectResponse
    {
        $role = $this->roleFromRoute($request);
        $submission = $this->submissionFromRouteKey($role, $submission);

        abort_unless(
            $submission
            && (int) $submission->user_id === (int) $request->user()->id
            && in_array($submission->status, UserTopbarNotifications::submissionNotificationStatuses(), true)
            && UserTopbarNotifications::submissionIsNotifiable($submission),
            404
        );

        $this->markSubmissionRead($request, $role, $submission);

        return redirect()->route("user.{$role}.submissions.show", $submission);
    }

    private function markRead(Request $request, string $role, int $recordId): void
    {
        UserNotificationRead::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'master_data_record_id' => $recordId,
                'role' => $role,
            ],
            ['read_at' => now()]
        );
    }

    private function markSubmissionRead(Request $request, string $role, Model $submission): void
    {
        UserSubmissionNotificationRead::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'role' => $role,
                'submission_type' => $submission::class,
                'submission_id' => $submission->getKey(),
                'status' => $submission->status,
                'notification_key' => UserTopbarNotifications::submissionNotificationKey($submission),
            ],
            ['read_at' => now()]
        );
    }

    private function submissionFromRouteKey(string $role, string $routeKey): ?Model
    {
        $model = $role === 'qc'
            ? new QcFormSubmission()
            : new CommissioningFormSubmission();

        return $model->resolveRouteBinding($routeKey);
    }

    private function roleFromRoute(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        return str_contains($routeName, '.commissioning.')
            ? 'commissioning'
            : 'qc';
    }

    private function recordMatchesRole(MasterDataRecord $record, string $role): bool
    {
        $category = $role === 'qc'
            ? MasterDataRecord::CATEGORY_QC
            : MasterDataRecord::CATEGORY_COMMISSIONING;

        return $record->document_category === $category
            && $record->status === 'active';
    }
}
