<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcFormSubmissionAttachment extends Model
{
    protected $fillable = [
        'qc_form_submission_id',
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
        return $this->belongsTo(QcFormSubmission::class, 'qc_form_submission_id');
    }
}
