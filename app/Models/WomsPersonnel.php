<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WomsPersonnel extends Model
{
    protected $table = 'woms_personnel';

    protected $fillable = [
        'woms_user_id',
        'name',
        'email',
        'present_in_source',
        'source_synced_at',
        'source_missing_at',
    ];

    protected function casts(): array
    {
        return [
            'present_in_source' => 'boolean',
            'source_synced_at' => 'datetime',
            'source_missing_at' => 'datetime',
        ];
    }
}
