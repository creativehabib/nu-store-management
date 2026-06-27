<?php

use App\Support\SettingManager;

if (! function_exists('setting')) {
    function setting(?string $key = null, $default = null)
    {
        if($key === null) {
            return SettingManager::class;
        }
        return SettingManager::get($key, $default);
    }
}

if (! function_exists('is_installed')) {
    function is_installed(): bool
    {
        return file_exists(storage_path('installed'));
    }
}

if(! function_exists('set_setting')) {
    function set_setting(string $key, $value, string $group = 'general'): void
    {
        SettingManager::set($key, $value, $group);
    }
}
