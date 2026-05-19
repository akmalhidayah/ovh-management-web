<?php

namespace App\Services;

use App\Models\DocumentNumberSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    public function generate(string $category, string $prefix, ?string $period = null, int $initialLastNumber = 0): string
    {
        return DB::transaction(function () use ($category, $prefix, $period, $initialLastNumber) {
            $period = $period ?: now()->format('m-Y');
            $initialLastNumber = max(0, $initialLastNumber);

            $sequence = DocumentNumberSequence::query()
                ->where('category', $category)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                DocumentNumberSequence::query()->insertOrIgnore([
                    'category' => $category,
                    'period' => $period,
                    'last_number' => $initialLastNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = DocumentNumberSequence::query()
                    ->where('category', $category)
                    ->where('period', $period)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            if ($sequence->last_number < $initialLastNumber) {
                $sequence->last_number = $initialLastNumber;
            }

            $sequence->last_number++;
            $sequence->save();

            $number = str_pad((string) $sequence->last_number, 3, '0', STR_PAD_LEFT);

            return "{$number}/{$prefix}/{$period}";
        });
    }

    public function preview(string $category, string $prefix, ?string $period = null, int $initialLastNumber = 0): string
    {
        $period = $period ?: now()->format('m-Y');
        $lastNumber = DocumentNumberSequence::query()
            ->where('category', $category)
            ->where('period', $period)
            ->value('last_number');

        $number = str_pad((string) (max((int) $lastNumber, $initialLastNumber) + 1), 3, '0', STR_PAD_LEFT);

        return "{$number}/{$prefix}/{$period}";
    }
}
