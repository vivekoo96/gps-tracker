<?php

use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('site_setting')) {
    function site_setting($key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = GlobalSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }
}
