<?php

namespace App\Services\Integrations;

use App\Models\User;
use App\Models\WomsDepartment;
use App\Models\WomsOrganizationSyncRun;
use App\Models\WomsPersonnel;
use App\Models\WomsSection;
use App\Models\WomsUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class WomsOrganizationSyncService
{
    private const LOCK_NAME = 'woms-organization-sync';

    public function __construct(
        private readonly WomsOrganizationApiClient $apiClient,
        private readonly WomsOrganizationSnapshotValidator $validator
    ) {}

    public function sync(
        string $triggeredByType,
        ?User $actor = null,
        bool $confirmLargeReduction = false,
        ?string $expectedChecksum = null
    ): WomsOrganizationSyncRun {
        $requestTimeout = min(30, max(1, (int) config('services.woms_organization.timeout_seconds', 15)));
        $lockSeconds = max(
            60,
            ($requestTimeout * 3) + 30,
            (int) config('services.woms_organization.lock_seconds', 300)
        );
        $lock = Cache::lock(self::LOCK_NAME, $lockSeconds);

        if (! $lock->get()) {
            return WomsOrganizationSyncRun::create([
                'status' => WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW,
                'started_at' => now(),
                'finished_at' => now(),
                'error_category' => 'concurrent_sync',
                'sanitized_error_message' => 'Sinkronisasi lain sedang berjalan. Tidak ada proses kedua yang dijalankan.',
                'triggered_by_type' => $triggeredByType,
                'triggered_by_user_id' => $actor?->id,
            ]);
        }

        $run = null;

        try {
            $run = WomsOrganizationSyncRun::create([
                'status' => WomsOrganizationSyncRun::STATUS_RUNNING,
                'started_at' => now(),
                'triggered_by_type' => $triggeredByType,
                'triggered_by_user_id' => $actor?->id,
            ]);
            $snapshot = $this->validator->validate($this->apiClient->fetchSnapshot());
            $this->fillSourceMetadata($run, $snapshot);

            if (
                $confirmLargeReduction
                && $expectedChecksum !== null
                && ! hash_equals(strtolower($expectedChecksum), $snapshot['meta']['checksum'])
            ) {
                $run->update([
                    'status' => WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW,
                    'finished_at' => now(),
                    'error_category' => 'snapshot_changed_after_review',
                    'sanitized_error_message' => 'Snapshot WOMS berubah setelah review. Periksa ulang perubahan terbaru sebelum mengonfirmasi.',
                ]);

                return $run->refresh();
            }

            $reductionWarning = $this->largeReductionWarning($snapshot);
            if ($reductionWarning !== null && ! $confirmLargeReduction) {
                $run->update([
                    'status' => WomsOrganizationSyncRun::STATUS_NEEDS_REVIEW,
                    'finished_at' => now(),
                    'error_category' => 'large_reduction',
                    'sanitized_error_message' => $reductionWarning,
                ]);

                return $run->refresh();
            }

            if ($this->isUnchangedAndLocallyConsistent($snapshot)) {
                $run->update([
                    'status' => WomsOrganizationSyncRun::STATUS_NO_CHANGES,
                    'finished_at' => now(),
                ]);

                return $run->refresh();
            }

            DB::transaction(function () use ($snapshot, $run): void {
                $changes = $this->persistSnapshot($snapshot);
                $run->update([
                    'status' => WomsOrganizationSyncRun::STATUS_SUCCESS,
                    'finished_at' => now(),
                    'created_count' => $changes['created'],
                    'updated_count' => $changes['updated'],
                    'marked_missing_count' => $changes['missing'],
                ]);
            });

            return $run->refresh();
        } catch (WomsOrganizationException $exception) {
            if (! $run) {
                throw $exception;
            }

            $this->markFailed($run, $exception->category, $exception->getMessage());

            return $run->refresh();
        } catch (Throwable $exception) {
            if (! $run) {
                throw $exception;
            }

            report($exception);
            $this->markFailed(
                $run,
                'internal_error',
                'Sinkronisasi gagal karena kesalahan internal. Data master lokal terakhir tetap dipertahankan.'
            );

            return $run->refresh();
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function fillSourceMetadata(WomsOrganizationSyncRun $run, array $snapshot): void
    {
        $run->update([
            'source_generated_at' => $snapshot['meta']['generated_at'],
            'source_checksum' => $snapshot['meta']['checksum'],
            'schema_version' => $snapshot['meta']['schema_version'],
            'department_count' => $snapshot['meta']['counts']['departments'],
            'unit_count' => $snapshot['meta']['counts']['units'],
            'section_count' => $snapshot['meta']['counts']['sections'],
            'personnel_count' => count($snapshot['personnel']),
        ]);
    }

    private function markFailed(WomsOrganizationSyncRun $run, string $category, string $message): void
    {
        $run->update([
            'status' => WomsOrganizationSyncRun::STATUS_FAILED,
            'finished_at' => now(),
            'error_category' => $category,
            'sanitized_error_message' => mb_substr($message, 0, 500),
        ]);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function largeReductionWarning(array $snapshot): ?string
    {
        $counts = $snapshot['meta']['counts'];
        $currentTotal = $counts['departments'] + $counts['units'] + $counts['sections'];

        if ($currentTotal === 0) {
            return 'Snapshot valid tetapi kosong. Sinkronisasi diblokir untuk mencegah penandaan hilang secara massal.';
        }

        $previous = WomsOrganizationSyncRun::query()
            ->whereIn('status', WomsOrganizationSyncRun::successfulStatuses())
            ->latest('finished_at')
            ->first();

        if (! $previous) {
            return null;
        }

        $threshold = min(100, max(0, (float) config('services.woms_organization.max_reduction_percent', 25)));
        $comparisons = [
            'departemen' => [(int) $previous->department_count, $counts['departments']],
            'unit kerja' => [(int) $previous->unit_count, $counts['units']],
            'seksi' => [(int) $previous->section_count, $counts['sections']],
        ];
        $warnings = [];

        foreach ($comparisons as $label => [$oldCount, $newCount]) {
            if ($oldCount <= 0 || $newCount >= $oldCount) {
                continue;
            }

            $reduction = (($oldCount - $newCount) / $oldCount) * 100;
            if ($reduction > $threshold) {
                $warnings[] = sprintf('%s turun dari %d menjadi %d (%.1f%%)', $label, $oldCount, $newCount, $reduction);
            }
        }

        if ($warnings === []) {
            return null;
        }

        return 'Perubahan besar terdeteksi: '.implode('; ', $warnings).'. Perlu konfirmasi Super Admin.';
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function isUnchangedAndLocallyConsistent(array $snapshot): bool
    {
        $lastSuccessful = WomsOrganizationSyncRun::query()
            ->whereIn('status', WomsOrganizationSyncRun::successfulStatuses())
            ->latest('finished_at')
            ->first();

        if (! $lastSuccessful || $lastSuccessful->source_checksum !== $snapshot['meta']['checksum']) {
            return false;
        }

        $counts = $snapshot['meta']['counts'];

        return WomsDepartment::present()->count() === $counts['departments']
            && WomsUnit::present()->count() === $counts['units']
            && WomsSection::present()->count() === $counts['sections']
            && WomsPersonnel::query()->where('present_in_source', true)->count() === count($snapshot['personnel'])
            && ! WomsUnit::present()->whereDoesntHave('department', fn ($query) => $query->present())->exists()
            && ! WomsSection::present()->whereDoesntHave('unit', fn ($query) => $query->present())->exists()
            && $this->localRowsMatch(
                WomsPersonnel::class,
                'woms_user_id',
                ['name', 'email'],
                $snapshot['personnel']
            )
            && $this->localRowsMatch(
                WomsDepartment::class,
                'woms_id',
                ['name', 'general_manager_woms_user_id'],
                $snapshot['departments']
            )
            && $this->localRowsMatch(
                WomsUnit::class,
                'woms_id',
                ['woms_department_id', 'name', 'senior_manager_woms_user_id'],
                $snapshot['units']
            )
            && $this->localRowsMatch(
                WomsSection::class,
                'woms_id',
                ['woms_unit_id', 'name', 'manager_woms_user_id'],
                $snapshot['sections']
            );
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $fields
     * @param  list<array<string, mixed>>  $sourceRows
     */
    private function localRowsMatch(string $modelClass, string $sourceKey, array $fields, array $sourceRows): bool
    {
        $localRows = $modelClass::query()
            ->where('present_in_source', true)
            ->get([$sourceKey, ...$fields])
            ->keyBy($sourceKey);

        foreach ($sourceRows as $sourceRow) {
            $local = $localRows->get($sourceRow[$sourceKey]);

            if (! $local) {
                return false;
            }

            foreach ($fields as $field) {
                if ((string) ($local->getAttribute($field) ?? '') !== (string) ($sourceRow[$field] ?? '')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array{created: int, updated: int, missing: int}
     */
    private function persistSnapshot(array $snapshot): array
    {
        $syncedAt = now();
        $created = 0;
        $updated = 0;

        $this->syncRows(WomsPersonnel::class, 'woms_user_id', $snapshot['personnel'], $syncedAt, $created, $updated);
        $this->syncRows(WomsDepartment::class, 'woms_id', $snapshot['departments'], $syncedAt, $created, $updated);
        $this->syncRows(WomsUnit::class, 'woms_id', $snapshot['units'], $syncedAt, $created, $updated);
        $this->syncRows(WomsSection::class, 'woms_id', $snapshot['sections'], $syncedAt, $created, $updated);

        $missing = 0;
        $missing += $this->markMissing(WomsSection::class, 'woms_id', array_column($snapshot['sections'], 'woms_id'), $syncedAt);
        $missing += $this->markMissing(WomsUnit::class, 'woms_id', array_column($snapshot['units'], 'woms_id'), $syncedAt);
        $missing += $this->markMissing(WomsDepartment::class, 'woms_id', array_column($snapshot['departments'], 'woms_id'), $syncedAt);
        $missing += $this->markMissing(WomsPersonnel::class, 'woms_user_id', array_column($snapshot['personnel'], 'woms_user_id'), $syncedAt);

        return compact('created', 'updated', 'missing');
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncRows(
        string $modelClass,
        string $sourceKey,
        array $rows,
        mixed $syncedAt,
        int &$created,
        int &$updated
    ): void {
        foreach ($rows as $row) {
            $model = $modelClass::query()->firstOrNew([$sourceKey => $row[$sourceKey]]);
            $wasExisting = $model->exists;
            $wasMissing = $wasExisting && ! (bool) $model->getAttribute('present_in_source');
            $model->fill($row);
            $businessChanged = $wasExisting && $model->isDirty(array_keys($row));
            $model->forceFill([
                'present_in_source' => true,
                'source_synced_at' => $syncedAt,
                'source_missing_at' => null,
            ])->save();

            if (! $wasExisting) {
                $created++;
            } elseif ($businessChanged || $wasMissing) {
                $updated++;
            }
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<int>  $sourceIds
     */
    private function markMissing(string $modelClass, string $sourceKey, array $sourceIds, mixed $missingAt): int
    {
        return $modelClass::query()
            ->where('present_in_source', true)
            ->whereNotIn($sourceKey, $sourceIds)
            ->update([
                'present_in_source' => false,
                'source_missing_at' => $missingAt,
                'updated_at' => $missingAt,
            ]);
    }
}
