<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateApprovalStep extends Model
{
    protected $fillable = [
        'template_type',
        'template_id',
        'step_order',
        'label',
        'is_submitter_signature',
        'requires_magic_link',
        'is_required',
    ];

    protected $casts = [
        'is_submitter_signature' => 'boolean',
        'requires_magic_link' => 'boolean',
        'is_required' => 'boolean',
    ];
}
