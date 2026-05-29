<?php

namespace App\Support;

use App\Models\AppSetting;

class PublicRegistrationAccess
{
    public const KEY = 'public_registration_enabled';

    public static function enabled(): bool
    {
        $setting = AppSetting::query()->where('key', self::KEY)->value('value');

        return $setting === null || filter_var($setting, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setEnabled(bool $enabled): void
    {
        AppSetting::updateOrCreate(
            ['key' => self::KEY],
            ['value' => $enabled ? '1' : '0']
        );
    }
}
