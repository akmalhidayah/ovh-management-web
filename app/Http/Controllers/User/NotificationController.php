<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MasterDataRecord;
use App\Models\UserNotificationRead;
use App\Support\UserTopbarNotifications;
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
