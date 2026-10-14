<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WomsUnit extends Model
{
    protected $fillable = [
        'woms_id',
        'woms_department_id',
        'name',
        'senior_manager_woms_user_id',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(WomsDepartment::class, 'woms_department_id', 'woms_id');
    }

    public function seniorManager(): BelongsTo
    {
        return $this->belongsTo(WomsPersonnel::class, 'senior_manager_woms_user_id', 'woms_user_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(WomsSection::class, 'woms_unit_id', 'woms_id');
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->where('present_in_source', true);
    }
}
