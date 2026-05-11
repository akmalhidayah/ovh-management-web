<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissioningFormTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'version',
        'status',
        'body_schema',
        'created_by',
    ];

    protected $casts = [
        'body_schema' => 'array',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(CommissioningFormSubmission::class);
    }
}
