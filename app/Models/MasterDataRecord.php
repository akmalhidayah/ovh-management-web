<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterDataRecord extends Model
{
    use HasFactory;

    public const CATEGORY_QC = 'qc';
    public const CATEGORY_COMMISSIONING = 'commissioning';

    protected $fillable = [
        'document_category',
        'year',
        'func_location',
        'equipment_no',
        'section_no',
        'description',
        'plant',
        'area',
        'organization_section_id',
        'status',
        'inspection_status',
        'notes',
        'created_by',
    ];

    public static function documentCategories(): array
    {
        return [
            self::CATEGORY_QC => 'QC',
            self::CATEGORY_COMMISSIONING => 'Commissioning',
        ];
    }

    public static function statuses(): array
    {
        return [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
        ];
    }

    public static function inspectionStatuses(): array
    {
        return [
            'close' => 'Close',
            'ongoing' => 'On Going',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organizationSection(): BelongsTo
    {
        return $this->belongsTo(OrganizationSection::class);
    }

    public function inspectionStatusHistories(): HasMany
    {
        return $this->hasMany(MasterDataInspectionStatusHistory::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('func_location', 'like', "%{$search}%")
                ->orWhere('equipment_no', 'like', "%{$search}%")
                ->orWhere('section_no', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('plant', 'like', "%{$search}%")
                ->orWhere('area', 'like', "%{$search}%");
        });
    }

    public function getDocumentCategoryLabelAttribute(): string
    {
        return self::documentCategories()[$this->document_category] ?? strtoupper($this->document_category);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }
}
