<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcFormTemplateTableRow extends Model
{
    protected $fillable = [
        'qc_form_template_id',
        'qc_form_template_block_id',
        'order_no',
        'row_data',
    ];

    protected $casts = [
        'row_data' => 'array',
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
