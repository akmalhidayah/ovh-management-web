<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberSequence extends Model
{
    protected $fillable = [
        'category',
        'period',
        'last_number',
    ];
}
