<?php

namespace App\Support;

use App\Models\CommissioningFormTemplate;
use App\Models\QcFormTemplate;

class TemplateSnapshot
{
    public static function forQc(QcFormTemplate $template): array
    {
        $template->loadMissing([
            'blocks.fields',
            'blocks.tableRows',
            'fields',
            'tableRows',
            'gridCells',
        ]);

        return [
            'id' => $template->id,
            'code' => $template->code ?? null,
            'name' => $template->name ?? $template->title ?? null,
            'version' => $template->version ?? null,
            'template_type' => $template->template_type ?? null,
            'body_schema' => $template->body_schema ?? null,
            'header_schema' => $template->getAttribute('header_schema'),
            'approval_schema' => $template->getAttribute('approval_schema'),
            'category' => $template->category ?? null,
            'blocks' => $template->blocks->map(fn ($block) => [
                'id' => $block->id,
                'type' => $block->type,
                'title' => $block->title,
                'order_no' => $block->order_no,
                'config' => $block->config ?? [],
                'fields' => $block->fields->map(fn ($field) => $field->only([
                    'id',
                    'field_name',
                    'label',
                    'type',
                    'required',
                    'readonly',
                    'options',
                    'unit',
                    'help_text',
                    'validation_rules',
                    'order_no',
                ]))->values()->all(),
                'table_rows' => $block->tableRows->map(fn ($row) => [
                    'id' => $row->id,
                    'order_no' => $row->order_no,
                    'row_data' => $row->row_data,
                ])->values()->all(),
            ])->values()->all(),
            'created_at' => $template->created_at?->toDateTimeString(),
            'updated_at' => $template->updated_at?->toDateTimeString(),
        ];
    }

    public static function forCommissioning(CommissioningFormTemplate $template): array
    {
        return [
            'id' => $template->id,
            'code' => $template->code ?? null,
            'name' => $template->name ?? $template->title ?? null,
            'version' => $template->version ?? null,
            'template_type' => $template->getAttribute('template_type'),
            'body_schema' => $template->body_schema ?? null,
            'header_schema' => $template->getAttribute('header_schema'),
            'approval_schema' => $template->getAttribute('approval_schema'),
            'category' => $template->category ?? null,
            'created_at' => $template->created_at?->toDateTimeString(),
            'updated_at' => $template->updated_at?->toDateTimeString(),
        ];
    }

    public static function majorVersion(mixed $version): ?int
    {
        if ($version === null || $version === '') {
            return null;
        }

        return preg_match('/\d+/', (string) $version, $matches) === 1
            ? (int) $matches[0]
            : null;
    }
}
