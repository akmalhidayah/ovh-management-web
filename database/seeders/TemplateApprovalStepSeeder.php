<?php

namespace Database\Seeders;

use App\Models\CommissioningFormTemplate;
use App\Models\QcFormTemplate;
use App\Models\TemplateApprovalStep;
use App\Support\Commissioning\FixedCommissioningTemplate;
use App\Support\QcTemplates\FixedQcTemplate;
use Illuminate\Database\Seeder;

class TemplateApprovalStepSeeder extends Seeder
{
    public function run(): void
    {
        QcFormTemplate::query()
            ->withTrashed()
            ->orderBy('id')
            ->get()
            ->each(function (QcFormTemplate $template): void {
                $type = FixedQcTemplate::normalizeType($template->template_type);
                $schema = FixedQcTemplate::normalizeSchema($type, $template->body_schema ?? []);

                collect(FixedQcTemplate::approvalColumnsWithDefaults($type, $schema['approval_defaults'] ?? []))
                    ->values()
                    ->each(function (array $column, int $index) use ($template): void {
                        $isSubmitter = $index === 0;

                        TemplateApprovalStep::updateOrCreate(
                            [
                                'template_type' => 'qc',
                                'template_id' => $template->id,
                                'step_order' => $index + 1,
                            ],
                            [
                                'label' => $column['label'],
                                'is_submitter_signature' => $isSubmitter,
                                'requires_magic_link' => ! $isSubmitter,
                                'is_required' => true,
                            ]
                        );
                    });
            });

        CommissioningFormTemplate::query()
            ->orderBy('id')
            ->get()
            ->each(function (CommissioningFormTemplate $template): void {
                collect(FixedCommissioningTemplate::approvalColumns())
                    ->values()
                    ->each(function (array $column, int $index) use ($template): void {
                        TemplateApprovalStep::updateOrCreate(
                            [
                                'template_type' => 'commissioning',
                                'template_id' => $template->id,
                                'step_order' => $index + 1,
                            ],
                            [
                                'label' => $column['label'],
                                'is_submitter_signature' => false,
                                'requires_magic_link' => true,
                                'is_required' => true,
                            ]
                        );
                    });
            });
    }
}
