<?php

namespace App\Services;

use App\Models\WomsSection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WomsOrganizationResolver
{
    /**
     * @return array{
     *     woms_section_id: int,
     *     woms_section_woms_id: int,
     *     section: string,
     *     woms_unit_woms_id: int,
     *     work_unit: string,
     *     woms_department_woms_id: int,
     *     department: string
     * }
     */
    public function resolvePresent(mixed $localSectionId, string $attribute = 'woms_section_id'): array
    {
        if (is_int($localSectionId) && $localSectionId > 0) {
            $sectionId = $localSectionId;
        } elseif (
            is_string($localSectionId)
            && preg_match('/^[1-9][0-9]*$/D', $localSectionId) === 1
            && (string) (int) $localSectionId === $localSectionId
        ) {
            $sectionId = (int) $localSectionId;
        } else {
            $this->invalid($attribute, 'Seksi WOMS yang dipilih tidak valid.');
        }

        $section = WomsSection::query()
            ->with('unit.department')
            ->find($sectionId);

        if (! $section) {
            $this->invalid($attribute, 'Seksi WOMS yang dipilih tidak ditemukan.');
        }

        if (! $section->present_in_source) {
            $this->invalid($attribute, 'Seksi WOMS yang dipilih sudah tidak tersedia di sumber.');
        }

        if (! $section->unit || ! $section->unit->present_in_source) {
            $this->invalid($attribute, 'Unit Kerja WOMS untuk seksi yang dipilih tidak tersedia.');
        }

        if (! $section->unit->department || ! $section->unit->department->present_in_source) {
            $this->invalid($attribute, 'Departemen WOMS untuk seksi yang dipilih tidak tersedia.');
        }

        return $this->hierarchy($section);
    }

    /**
     * @return Collection<int, array<string, int|string>>
     */
    public function presentOptions(): Collection
    {
        return WomsSection::query()
            ->present()
            ->whereHas('unit', fn ($query) => $query
                ->present()
                ->whereHas('department', fn ($departmentQuery) => $departmentQuery->present()))
            ->with('unit.department')
            ->get()
            ->map(fn (WomsSection $section): array => $this->hierarchy($section))
            ->sortBy(fn (array $item): string => implode('|', [
                $item['department'],
                $item['work_unit'],
                $item['section'],
            ]))
            ->values();
    }

    /**
     * Build a historical description without requiring present_in_source=true.
     *
     * @return array<string, int|string>|null
     */
    public function describeHistorical(?WomsSection $section): ?array
    {
        if (! $section) {
            return null;
        }

        $section->loadMissing('unit.department');

        if (! $section->unit || ! $section->unit->department) {
            return null;
        }

        return $this->hierarchy($section);
    }

    /**
     * @return array<string, int|string>
     */
    private function hierarchy(WomsSection $section): array
    {
        $unit = $section->unit;
        $department = $unit->department;

        return [
            'woms_section_id' => (int) $section->id,
            'woms_section_woms_id' => (int) $section->woms_id,
            'section' => (string) $section->name,
            'woms_unit_woms_id' => (int) $unit->woms_id,
            'work_unit' => (string) $unit->name,
            'woms_department_woms_id' => (int) $department->woms_id,
            'department' => (string) $department->name,
        ];
    }

    private function invalid(string $attribute, string $message): never
    {
        throw ValidationException::withMessages([$attribute => $message]);
    }
}
