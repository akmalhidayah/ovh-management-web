<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QcFormSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'qc_form_template_id',
        'user_id',
        'form_number',
        'status',
        'submitted_at',
        'year',
        'plant',
        'area',
        'equipment',
        'report_no',
        'ovh_plant',
        'unit',
        'tag_num',
        'tgl_mulai',
        'pekerjaan',
        'durasi',
        'general_info',
        'body_data',
        'note',
        'approval_data',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'tgl_mulai' => 'date',
        'general_info' => 'array',
        'body_data' => 'array',
        'approval_data' => 'array',
    ];

    public function getRouteKey(): mixed
    {
        $formNumber = (string) $this->getAttribute('form_number');

        return $formNumber !== ''
            ? self::routeKeyFromFormNumber($formNumber)
            : parent::getRouteKey();
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return $this->newQuery()->where($field, $value)->first();
        }

        return $this->newQuery()
            ->where('form_number', self::formNumberFromRouteKey((string) $value))
            ->first();
    }

    public static function routeKeyFromFormNumber(string $formNumber): string
    {
        return str_replace(['/', '\\'], '-', trim($formNumber));
    }

    public static function formNumberFromRouteKey(string $routeKey): string
    {
        if (preg_match('/^(.+)-QC-(\d{2}-\d{4})$/', $routeKey, $matches) === 1) {
            return "{$matches[1]}/QC/{$matches[2]}";
        }

        return $routeKey;
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QcFormTemplate::class, 'qc_form_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(QcFormSubmissionRow::class)->orderBy('order_no');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(QcFormSubmissionAttachment::class);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->whereIn('status', ['submitted', 'approved', 'revision']);
    }
}
