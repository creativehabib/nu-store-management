<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingManager
{
    protected static array $runtime = [];
    protected static ?array $autoloadSettings = null;
    protected static array $groupCache = [];

    public static function get(string $key, $default = null)
    {
        if (! is_installed()) {
            return $default;
        }

        if (array_key_exists($key, self::$runtime)) {
            return self::$runtime[$key];
        }

        $settings = self::autoloadSettings();

        if (! array_key_exists($key, $settings)) {
            self::$runtime[$key] = $default;
            return $default;
        }

        $value = $settings[$key];
        self::$runtime[$key] = $value;

        return $value;
    }

    public static function set(string $key, $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::encode($value),
                'group' => $group,
                'autoload' => true,
            ]
        );

        self::flushCache();
        self::$runtime[$key] = $value;
    }

    public static function group(string $group): array
    {
        if (! is_installed()) {
            return [];
        }

        if (array_key_exists($group, self::$groupCache)) {
            return self::$groupCache[$group];
        }

        $settings = Cache::rememberForever("settings.group.{$group}", function () use ($group) {
            return Setting::where('group', $group)
                ->pluck('value', 'key')
                ->map(fn ($value) => self::decode($value))
                ->toArray();
        });

        self::$groupCache[$group] = $settings;

        return $settings;
    }

    public static function flushCache(): void
    {
        Cache::forget('settings.autoload');

        foreach (array_keys(self::$groupCache) as $group) {
            Cache::forget("settings.group.{$group}");
        }

        self::$runtime = [];
        self::$autoloadSettings = null;
        self::$groupCache = [];
    }

    protected static function autoloadSettings(): array
    {
        if (self::$autoloadSettings !== null) {
            return self::$autoloadSettings;
        }

        self::$autoloadSettings = Cache::rememberForever('settings.autoload', function () {
            return Setting::where('autoload', true)
                ->pluck('value', 'key')
                ->map(fn ($value) => self::decode($value))
                ->toArray();
        });

        return self::$autoloadSettings;
    }

    protected static function encode($value): ?string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        return $value;
    }

    protected static function decode(?string $value)
    {
        if ($value === null) {
            return null;
        }

        $json = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $json : $value;
    }
}
