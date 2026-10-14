<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrganizationSection extends Model
{
    protected $fillable = [
        'department',
        'unit_kerja',
        'section',
        'status',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function womsMapping(): HasOne
    {
        return $this->hasOne(OrganizationSectionMapping::class);
    }
}
