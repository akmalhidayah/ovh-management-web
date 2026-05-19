<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\TemplateApprovalStepSeeder;

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
