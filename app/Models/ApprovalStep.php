<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalStep extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'approval_flow_id',
        'step_order',
        'label',
        'is_submitter_signature',
        'requires_magic_link',
        'status',
        'approver_name',
        'approver_position',
        'approver_email',
        'approver_phone',
        'signature_path',
        'signature_data',
        'reject_reason',
        'acted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_submitter_signature' => 'boolean',
        'requires_magic_link' => 'boolean',
        'acted_at' => 'datetime',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ApprovalLink::class);
    }

    public function activeLink(): ?ApprovalLink
    {
        return $this->links()
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}
