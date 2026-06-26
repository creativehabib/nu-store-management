<?php

use App\Support\SettingManager;

if (! function_exists('setting')) {
    /**
     * Get a setting value.
     */
    function setting(string $key, $default = null)
    {
        return SettingManager::get($key, $default);
    }
}

if (! function_exists('set_setting')) {
    /**
     * Set a setting value.
     */
    function set_setting(string $key, $value): void
    {
        SettingManager::set($key, $value);
    }
}

if (! function_exists('is_installed')) {
    /**
     * Check if the application is installed.
     */
    function is_installed(): bool
    {
        return file_exists(storage_path('installed'));
    }
}
