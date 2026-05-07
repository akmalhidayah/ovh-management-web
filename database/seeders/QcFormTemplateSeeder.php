<?php

namespace Database\Seeders;

use App\Models\QcFormTemplate;
use App\Support\QcTemplates\QcTemplateRegistry;
use App\Support\QcTemplates\TemplateBuilder;
use Illuminate\Database\Seeder;

class QcFormTemplateSeeder extends Seeder
{
    public function run(): void
    {
        QcFormTemplate::whereIn('code', ['QCR-CR-001'])->delete();

        foreach (QcTemplateRegistry::all() as $preset) {
            TemplateBuilder::createExcelGridTemplate($preset);
        }
    }
}
