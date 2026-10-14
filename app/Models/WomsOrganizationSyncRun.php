<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WomsOrganizationSyncRun extends Model
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_NO_CHANGES = 'no_changes';
    public const STATUS_FAILED = 'failed';
    public const STATUS_NEEDS_REVIEW = 'needs_review';

    public const TRIGGER_COMMAND = 'command';
    public const TRIGGER_SCHEDULER = 'scheduler';
    public const TRIGGER_ADMIN = 'admin';

    protected $fillable = [
        'status',
        'started_at',
        'finished_at',
        'source_generated_at',
        'source_checksum',
        'schema_version',
        'department_count',
        'unit_count',
        'section_count',
        'personnel_count',
        'created_count',
        'updated_count',
        'marked_missing_count',
        'error_category',
        'sanitized_error_message',
        'triggered_by_type',
        'triggered_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'source_generated_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public static function successfulStatuses(): array
    {
        return [self::STATUS_SUCCESS, self::STATUS_NO_CHANGES];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_RUNNING => 'Sedang berjalan',
            self::STATUS_SUCCESS => 'Berhasil',
            self::STATUS_NO_CHANGES => 'Tidak ada perubahan',
            self::STATUS_NEEDS_REVIEW => 'Perlu review',
            default => 'Gagal',
        };
    }
}
