<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissioningFormSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commissioning_form_template_id',
        'template_code',
        'template_name',
        'template_version',
        'template_snapshot',
        'user_id',
        'form_number',
        'status',
        'submitted_at',
        'year',
        'area',
        'equipment',
        'equipment_no',
        'tag_num',
        'functional_location',
        'header_data',
        'body_data',
        'note',
        'approval_data',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'header_data' => 'array',
        'body_data' => 'array',
        'approval_data' => 'array',
        'template_snapshot' => 'array',
    ];

    public function getRouteKey(): mixed
    {
        return $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return $this->newQuery()->where($field, $value)->first();
        }

        $routeKey = trim((string) $value);

        if (ctype_digit($routeKey)) {
            return $this->newQuery()->whereKey((int) $routeKey)->first();
        }

        $formNumber = self::formNumberFromRouteKey($routeKey);
        $query = $this->newQuery()->where('form_number', $formNumber);

        if (request()->routeIs('user.commissioning.*') && auth()->check()) {
            $ownSubmission = (clone $query)
                ->where('user_id', auth()->id())
                ->first();

            if ($ownSubmission) {
                return $ownSubmission;
            }
        }

        return $query->first();
    }

    public static function routeKeyFromFormNumber(string $formNumber): string
    {
        return str_replace(['/', '\\'], '-', trim($formNumber));
    }

    public static function formNumberFromRouteKey(string $routeKey): string
    {
        if (preg_match('/^(.+)-COM-(\d{2}-\d{4})$/', $routeKey, $matches) === 1) {
            return "{$matches[1]}/COM/{$matches[2]}";
        }

        return $routeKey;
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CommissioningFormTemplate::class, 'commissioning_form_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommissioningFormSubmissionAttachment::class);
    }

    public function approvalFlow(): MorphOne
    {
        return $this->morphOne(ApprovalFlow::class, 'approvable')->latestOfMany();
    }
}
