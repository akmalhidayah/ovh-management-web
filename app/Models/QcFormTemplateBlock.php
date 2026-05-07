<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcFormTemplateBlock extends Model
{
    protected $fillable = [
        'qc_form_template_id',
        'type',
        'title',
        'order_no',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(QcFormTemplate::class, 'qc_form_template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(QcFormTemplateField::class)->orderBy('order_no');
    }

    public function tableRows(): HasMany
    {
        return $this->hasMany(QcFormTemplateTableRow::class)->orderBy('order_no');
    }
}
