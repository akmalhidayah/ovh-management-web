<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WomsDepartment extends Model
{
    protected $fillable = [
        'woms_id',
        'name',
        'general_manager_woms_user_id',
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

    public function generalManager(): BelongsTo
    {
        return $this->belongsTo(WomsPersonnel::class, 'general_manager_woms_user_id', 'woms_user_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(WomsUnit::class, 'woms_department_id', 'woms_id');
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->where('present_in_source', true);
    }
}
