<?php

namespace App\Services\Integrations;

use DateTimeImmutable;
use Throwable;

class WomsOrganizationSnapshotValidator
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function validate(array $payload): array
    {
        if (($payload['success'] ?? null) !== true) {
            $this->invalid('Field success pada snapshot WOMS harus bernilai true.');
        }

        $meta = $this->requiredArray($payload, 'meta', 'meta');
        $data = $this->requiredArray($payload, 'data', 'data');

        if (($meta['complete'] ?? null) !== true) {
            $this->invalid('Snapshot WOMS tidak ditandai lengkap.');
        }

        if (($meta['source'] ?? null) !== 'WOMS') {
            $this->invalid('Sumber snapshot organisasi bukan WOMS.');
        }

        if (($meta['schema_version'] ?? null) !== 1) {
            $this->invalid('Versi schema snapshot WOMS tidak didukung.');
        }

        $checksum = $meta['checksum'] ?? null;
        if (! is_string($checksum) || preg_match('/\A[a-f0-9]{64}\z/i', $checksum) !== 1) {
            $this->invalid('Checksum snapshot WOMS bukan SHA-256 hexadecimal yang valid.');
        }

        $sourceGeneratedAt = $this->validTimestamp($meta['generated_at'] ?? null);
        $counts = $this->requiredArray($meta, 'counts', 'meta.counts');
        $expectedCounts = [
            'departments' => $this->validCount($counts['departments'] ?? null, 'departments'),
            'units' => $this->validCount($counts['units'] ?? null, 'units'),
            'sections' => $this->validCount($counts['sections'] ?? null, 'sections'),
        ];
        $departmentPayloads = $data['departments'] ?? null;

        if (! is_array($departmentPayloads) || ! array_is_list($departmentPayloads)) {
            $this->invalid('data.departments harus berupa array JSON.');
        }

        $departments = [];
        $units = [];
        $sections = [];
        $personnel = [];
        $departmentIds = [];
        $unitIds = [];
        $sectionIds = [];

        foreach ($departmentPayloads as $departmentIndex => $departmentPayload) {
            if (! is_array($departmentPayload)) {
                $this->invalid("Departemen pada indeks {$departmentIndex} harus berupa object.");
            }

            $departmentId = $this->validId($departmentPayload['id'] ?? null, "department[{$departmentIndex}].id");
            $this->assertUniqueId($departmentIds, $departmentId, 'departemen');
            $generalManagerId = $this->personnelId(
                $this->requiredNullableObject($departmentPayload, 'general_manager', "department[{$departmentIndex}].general_manager"),
                $personnel,
                "department[{$departmentIndex}].general_manager"
            );
            $departmentName = $this->validName($departmentPayload['name'] ?? null, "department[{$departmentIndex}].name");
            $unitPayloads = $departmentPayload['units'] ?? null;

            if (! is_array($unitPayloads) || ! array_is_list($unitPayloads)) {
                $this->invalid("Unit pada departemen {$departmentId} harus berupa array JSON.");
            }

            $departments[] = [
                'woms_id' => $departmentId,
                'name' => $departmentName,
                'general_manager_woms_user_id' => $generalManagerId,
            ];

            foreach ($unitPayloads as $unitIndex => $unitPayload) {
                if (! is_array($unitPayload)) {
                    $this->invalid("Unit pada departemen {$departmentId} indeks {$unitIndex} harus berupa object.");
                }

                $unitId = $this->validId($unitPayload['id'] ?? null, "unit[{$unitIndex}].id");
                $this->assertUniqueId($unitIds, $unitId, 'unit kerja');
                $unitDepartmentId = $this->validId($unitPayload['department_id'] ?? null, "unit[{$unitIndex}].department_id");

                if ($unitDepartmentId !== $departmentId) {
                    $this->invalid("Parent departemen untuk unit WOMS {$unitId} tidak konsisten.");
                }

                $seniorManagerId = $this->personnelId(
                    $this->requiredNullableObject($unitPayload, 'senior_manager', "unit[{$unitIndex}].senior_manager"),
                    $personnel,
                    "unit[{$unitIndex}].senior_manager"
                );
                $sectionPayloads = $unitPayload['sections'] ?? null;

                if (! is_array($sectionPayloads) || ! array_is_list($sectionPayloads)) {
                    $this->invalid("Seksi pada unit WOMS {$unitId} harus berupa array JSON.");
                }

                $units[] = [
                    'woms_id' => $unitId,
                    'woms_department_id' => $departmentId,
                    'name' => $this->validName($unitPayload['name'] ?? null, "unit[{$unitIndex}].name"),
                    'senior_manager_woms_user_id' => $seniorManagerId,
                ];

                foreach ($sectionPayloads as $sectionIndex => $sectionPayload) {
                    if (! is_array($sectionPayload)) {
                        $this->invalid("Seksi pada unit {$unitId} indeks {$sectionIndex} harus berupa object.");
                    }

                    $sectionId = $this->validId($sectionPayload['id'] ?? null, "section[{$sectionIndex}].id");
                    $this->assertUniqueId($sectionIds, $sectionId, 'seksi');
                    $sectionUnitId = $this->validId($sectionPayload['unit_work_id'] ?? null, "section[{$sectionIndex}].unit_work_id");

                    if ($sectionUnitId !== $unitId) {
                        $this->invalid("Parent unit untuk seksi WOMS {$sectionId} tidak konsisten.");
                    }

                    $managerId = $this->personnelId(
                        $this->requiredNullableObject($sectionPayload, 'manager', "section[{$sectionIndex}].manager"),
                        $personnel,
                        "section[{$sectionIndex}].manager"
                    );
                    $sections[] = [
                        'woms_id' => $sectionId,
                        'woms_unit_id' => $unitId,
                        'name' => $this->validName($sectionPayload['name'] ?? null, "section[{$sectionIndex}].name"),
                        'manager_woms_user_id' => $managerId,
                    ];
                }
            }
        }

        $actualCounts = [
            'departments' => count($departments),
            'units' => count($units),
            'sections' => count($sections),
        ];

        if ($actualCounts !== $expectedCounts) {
            $this->invalid('Jumlah entitas hasil parsing tidak sama dengan meta.counts WOMS.');
        }

        return [
            'meta' => [
                'source' => 'WOMS',
                'schema_version' => 1,
                'generated_at' => $sourceGeneratedAt,
                'checksum' => strtolower($checksum),
                'counts' => $actualCounts,
            ],
            'departments' => $departments,
            'units' => $units,
            'sections' => $sections,
            'personnel' => array_values($personnel),
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function requiredArray(array $source, string $key, string $path): array
    {
        if (! isset($source[$key]) || ! is_array($source[$key])) {
            $this->invalid("{$path} harus berupa object JSON.");
        }

        return $source[$key];
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>|null
     */
    private function requiredNullableObject(array $source, string $key, string $path): ?array
    {
        if (! array_key_exists($key, $source)) {
            $this->invalid("Field {$path} tidak tersedia.");
        }

        if ($source[$key] !== null && ! is_array($source[$key])) {
            $this->invalid("{$path} harus berupa object atau null.");
        }

        return $source[$key];
    }

    private function validId(mixed $value, string $path): int
    {
        if (! is_int($value) || $value <= 0) {
            $this->invalid("{$path} harus berupa integer positif.");
        }

        return $value;
    }

    private function validCount(mixed $value, string $name): int
    {
        if (! is_int($value) || $value < 0) {
            $this->invalid("meta.counts.{$name} harus berupa integer non-negatif.");
        }

        return $value;
    }

    private function validName(mixed $value, string $path): string
    {
        if (! is_string($value) || trim($value) === '' || mb_strlen(trim($value)) > 255) {
            $this->invalid("{$path} harus berupa string yang valid.");
        }

        return trim($value);
    }

    private function validTimestamp(mixed $value): string
    {
        if (
            ! is_string($value)
            || preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})\z/', trim($value)) !== 1
        ) {
            $this->invalid('meta.generated_at harus berupa timestamp yang valid.');
        }

        try {
            return (new DateTimeImmutable($value))->format(DATE_ATOM);
        } catch (Throwable) {
            $this->invalid('meta.generated_at harus berupa timestamp yang valid.');
        }
    }

    /**
     * @param  array<int, true>  $ids
     */
    private function assertUniqueId(array &$ids, int $id, string $entity): void
    {
        if (isset($ids[$id])) {
            $this->invalid("ID {$entity} WOMS {$id} ditemukan lebih dari satu kali.");
        }

        $ids[$id] = true;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @param  array<int, array<string, mixed>>  $personnel
     */
    private function personnelId(?array $payload, array &$personnel, string $path): ?int
    {
        if ($payload === null) {
            return null;
        }

        $id = $this->validId($payload['id'] ?? null, "{$path}.id");
        $name = $this->validName($payload['name'] ?? null, "{$path}.name");
        $email = $payload['email'] ?? null;

        if ($email !== null) {
            if (! is_string($email) || mb_strlen(trim($email)) > 255 || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                $this->invalid("{$path}.email harus berupa alamat email yang valid atau null.");
            }

            $email = strtolower(trim($email));
        }

        if (isset($personnel[$id])) {
            $existing = $personnel[$id];
            $emailsConflict = $existing['email'] !== null && $email !== null && $existing['email'] !== $email;

            if ($existing['name'] !== $name || $emailsConflict) {
                $this->invalid("Identitas personel WOMS {$id} tidak konsisten antar jabatan.");
            }

            if ($existing['email'] === null && $email !== null) {
                $personnel[$id]['email'] = $email;
            }

            return $id;
        }

        $personnel[$id] = [
            'woms_user_id' => $id,
            'name' => $name,
            'email' => $email,
        ];

        return $id;
    }

    private function invalid(string $message): never
    {
        throw new WomsOrganizationException('invalid_snapshot', $message.' Data lokal tidak diubah.');
    }
}
