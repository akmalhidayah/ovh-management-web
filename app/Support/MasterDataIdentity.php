<?php

namespace App\Support;

class MasterDataIdentity
{
    public static function usableEquipmentNumber(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || preg_match('/^0+$/', $value) === 1) {
            return null;
        }

        return $value;
    }

    public static function equipmentNumbersMatch(mixed $left, mixed $right): bool
    {
        $left = self::usableEquipmentNumber($left);
        $right = self::usableEquipmentNumber($right);

        return $left !== null && $right !== null && $left === $right;
    }
}
