<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissioningFormSubmissionAttachment extends Model
{
    protected $fillable = [
        'field_key',
        'label',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'type',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CommissioningFormSubmission::class, 'commissioning_form_submission_id');
    }
}
