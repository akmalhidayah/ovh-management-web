<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcFormTemplateGridCell extends Model
{
    protected $fillable = [
        'qc_form_template_id',
        'qc_form_template_block_id',
        'row_start',
        'col_start',
        'row_span',
        'col_span',
        'cell_type',
        'field_name',
        'label',
        'value_default',
        'input_type',
        'options',
        'readonly',
        'css_class',
        'style',
        'order_no',
    ];

    protected $casts = [
        'options' => 'array',
        'readonly' => 'boolean',
        'style' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QcFormTemplate::class, 'qc_form_template_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(QcFormTemplateBlock::class, 'qc_form_template_block_id');
    }
}
