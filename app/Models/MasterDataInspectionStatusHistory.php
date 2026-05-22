<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MasterDataInspectionStatusHistory extends Model
{
    protected $fillable = [
        'master_data_record_id',
        'previous_status',
        'status',
        'source',
        'submission_type',
        'submission_id',
        'changed_by',
        'changed_at',
        'snapshot',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'snapshot' => 'array',
    ];

    public function masterDataRecord(): BelongsTo
    {
        return $this->belongsTo(MasterDataRecord::class);
    }

    public function submission(): MorphTo
    {
        return $this->morphTo();
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
