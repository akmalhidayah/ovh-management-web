<?php

namespace App\Support\QcTemplates;

class PresetBlocks
{
    public static function make(array $meta, array $checklistRows = [], array $options = []): array
    {
        $approval = $options['approval'] ?? [];
        $blocks = [
            [
                'type' => 'general_info',
                'title' => 'Informasi Umum',
                'fields' => TemplateBuilder::generalInfoFields($meta),
            ],
            [
                'type' => 'checklist_table',
                'title' => 'Item Pengecekan',
                'columns' => TemplateBuilder::defaultChecklistColumns(),
                'rows' => $checklistRows,
            ],
            [
                'type' => 'note',
                'title' => 'Catatan',
                'field' => ['name' => 'catatan_umum', 'label' => 'Catatan', 'type' => 'textarea'],
            ],
            [
                'type' => 'approval',
                'title' => $approval['title'] ?? 'Approval',
                'columns' => $approval['columns'] ?? TemplateBuilder::defaultApprovalColumns(),
                'notes' => $approval['notes'] ?? TemplateBuilder::defaultApprovalNotes(),
            ],
        ];

        if (! empty($options['attachment'])) {
            $attachment = $options['attachment'];
            array_splice($blocks, 3, 0, [[
                'type' => 'attachment',
                'title' => $attachment['title'] ?? 'Lampiran',
                'description' => $attachment['description'] ?? null,
                'fields' => $attachment['fields'] ?? [],
            ]]);
        }

        return $blocks;
    }
}
