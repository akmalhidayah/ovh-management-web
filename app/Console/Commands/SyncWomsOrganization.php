<?php

namespace App\Console\Commands;

use App\Models\WomsOrganizationSyncRun;
use App\Services\Integrations\WomsOrganizationSyncService;
use Illuminate\Console\Command;

class SyncWomsOrganization extends Command
{
    protected $signature = 'organization:sync-woms
        {--confirm-large-reduction : Izinkan perubahan besar yang sudah diperiksa manual}
        {--scheduled : Tandai bahwa command dipanggil oleh Laravel Scheduler}';

    protected $description = 'Synchronize the local organization master from the read-only WOMS API';

    public function handle(WomsOrganizationSyncService $syncService): int
    {
        $this->newLine();
        $this->info('WOMS Organization Sync');

        $run = $syncService->sync(
            $this->option('scheduled')
                ? WomsOrganizationSyncRun::TRIGGER_SCHEDULER
                : WomsOrganizationSyncRun::TRIGGER_COMMAND,
            confirmLargeReduction: (bool) $this->option('confirm-large-reduction')
        );

        $this->table(['Item', 'Hasil'], [
            ['Source', 'WOMS'],
            ['Schema', $run->schema_version ?? '-'],
            ['Departments', $run->department_count],
            ['Units', $run->unit_count],
            ['Sections', $run->section_count],
            ['Personnel', $run->personnel_count],
            ['Status', strtoupper(str_replace('_', ' ', $run->status))],
            ['Created', $run->created_count],
            ['Updated', $run->updated_count],
            ['Missing', $run->marked_missing_count],
            ['Checksum', $run->source_checksum ?? '-'],
        ]);

        if ($run->sanitized_error_message) {
            $this->warn($run->sanitized_error_message);
        }

        return in_array($run->status, WomsOrganizationSyncRun::successfulStatuses(), true)
            ? self::SUCCESS
            : self::FAILURE;
    }
}
