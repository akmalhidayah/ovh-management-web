<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcFormSubmissionRow extends Model
{
    protected $fillable = [
        'qc_form_submission_id',
        'qc_form_template_block_id',
        'block_type',
        'order_no',
        'row_data',
        'status_value',
        'catatan',
        'aktual',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(QcFormSubmission::class, 'qc_form_submission_id');
    }
}
