<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QcFormTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'version',
        'status',
        'layout_mode',
        'template_type',
        'body_schema',
        'source_file',
        'created_by',
    ];

    protected $casts = [
        'body_schema' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(QcFormTemplateBlock::class)->orderBy('order_no');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(QcFormTemplateField::class)->orderBy('order_no');
    }

    public function tableRows(): HasMany
    {
        return $this->hasMany(QcFormTemplateTableRow::class)->orderBy('order_no');
    }

    public function gridCells(): HasMany
    {
        return $this->hasMany(QcFormTemplateGridCell::class)->orderBy('order_no');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(TemplateApprovalStep::class, 'template_id')
            ->where('template_type', 'qc')
            ->orderBy('step_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }
}
