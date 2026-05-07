<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcFormTemplateField extends Model
{
    protected $fillable = [
        'qc_form_template_id',
        'qc_form_template_block_id',
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
    ];

    protected $casts = [
        'required' => 'boolean',
        'readonly' => 'boolean',
        'options' => 'array',
        'validation_rules' => 'array',
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
