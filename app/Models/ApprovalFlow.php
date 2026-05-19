<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalFlow extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REVISION_REQUIRED = 'revision_required';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'current_step_order',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApprovalEvent::class);
    }
}
