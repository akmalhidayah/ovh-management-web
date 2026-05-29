<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationRead extends Model
{
    protected $fillable = [
        'user_id',
        'master_data_record_id',
        'role',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function masterDataRecord(): BelongsTo
    {
        return $this->belongsTo(MasterDataRecord::class);
    }
}
