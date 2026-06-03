<?php

namespace App\Support;

class AreaOwnerLabel
{
    public static function fieldLabel(): string
    {
        return 'Area Owner';
    }

    public static function approvalLabel(?string $value, ?string $fallback = null): string
    {
        $label = trim((string) $value);

        if ($label === '' || self::isPlaceholder($label)) {
            $fallbackLabel = trim((string) $fallback);

            if ($fallbackLabel !== '' && ! self::isPlaceholder($fallbackLabel)) {
                return self::approvalLabel($fallbackLabel);
            }

            return self::fieldLabel();
        }

        if (preg_match('/^mgr\s+of\s+(.+)$/i', $label, $matches)) {
            return 'Mgr of '.trim($matches[1]);
        }

        return 'Mgr of '.$label;
    }

    public static function isPlaceholder(string $label): bool
    {
        return in_array(strtoupper(trim($label)), [
            'UNIT KERJA',
            'AREA OWNER',
            'MGR OF AREA OWNER',
        ], true);
    }
}
