<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingHelper
{
    public static function get(string $key, $default = null)
    {
        $settings = Cache::rememberForever('site_settings', function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });

        if (! is_array($settings)) {
            Cache::forget('site_settings');
            $settings = Setting::all()->pluck('value', 'key')->toArray();
            Cache::put('site_settings', $settings);
        }

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }

    public static function forgetCache(): void
    {
        Cache::forget('site_settings');
    }
}
