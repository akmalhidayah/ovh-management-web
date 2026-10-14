<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WomsSection extends Model
{
    protected $fillable = [
        'woms_id',
        'woms_unit_id',
        'name',
        'manager_woms_user_id',
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(WomsUnit::class, 'woms_unit_id', 'woms_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(WomsPersonnel::class, 'manager_woms_user_id', 'woms_user_id');
    }

    public function legacyMapping(): HasOne
    {
        return $this->hasOne(OrganizationSectionMapping::class);
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->where('present_in_source', true);
    }
}
