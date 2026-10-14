<?php

use Database\Seeders\TemplateApprovalStepSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('approval:seed-default-steps', function () {
    $this->call('db:seed', [
        '--class' => TemplateApprovalStepSeeder::class,
        '--force' => true,
    ]);

    $this->info('Default approval steps seeded for QC and Commissioning templates.');
})->purpose('Seed default approval step configuration for existing QC and Commissioning templates');

if ((bool) config('services.woms_organization.sync_enabled', false)) {
    $interval = min(60, max(1, (int) config('services.woms_organization.sync_interval_minutes', 30)));
    $lockMinutes = max(2, (int) ceil((int) config('services.woms_organization.lock_seconds', 300) / 60));
    $cronExpression = $interval === 60 ? '0 * * * *' : "*/{$interval} * * * *";

    Schedule::command('organization:sync-woms --scheduled')
        ->cron($cronExpression)
        ->name('woms-organization-sync')
        ->withoutOverlapping($lockMinutes);
}
