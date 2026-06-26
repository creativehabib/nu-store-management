<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingManager
{
    protected static array $runtime = [];
    protected static ?array $allSettings = null;

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        // ১. রানটাইম ক্যাশ চেক (একই রিকুয়েস্টে বারবার ডাটাবেস কল এড়াতে)
        if (array_key_exists($key, self::$runtime)) {
            return self::$runtime[$key];
        }

        // ২. সব সেটিং ক্যাশ থেকে নিয়ে আসা
        $settings = self::getAllSettings();

        // ৩. যদি সেটিং না থাকে, ডিফল্ট ভ্যালু রিটার্ন করা
        if (! array_key_exists($key, $settings)) {
            self::$runtime[$key] = $default;
            return $default;
        }

        // ৪. রানটাইমে সেভ করা এবং রিটার্ন করা
        $value = $settings[$key];
        self::$runtime[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => self::encode($value)]
        );

        // সেভ করার পর ক্যাশ ফ্লাশ করা যাতে নতুন ডাটা লোড হয়
        self::flushCache();
        self::$runtime[$key] = $value;
    }

    /**
     * Fetch all settings from cache or database.
     */
    protected static function getAllSettings(): array
    {
        if (self::$allSettings !== null) {
            return self::$allSettings;
        }

        // ডাটাবেস মাইগ্রেশন রান করার আগে এরর এড়াতে Schema চেক
        if (! Schema::hasTable('settings')) {
            return [];
        }

        // ক্যাশে চিরকালের জন্য সেভ করা (যতক্ষণ না আপডেট হচ্ছে)
        self::$allSettings = Cache::rememberForever('settings.all', function () {
            return Setting::pluck('value', 'key')
                ->map(fn ($value) => self::decode($value))
                ->toArray();
        });

        return self::$allSettings;
    }

    /**
     * Clear all settings cache.
     */
    public static function flushCache(): void
    {
        Cache::forget('settings.all');
        self::$allSettings = null;
        self::$runtime = [];
    }

    /**
     * Encode value before saving to database.
     */
    protected static function encode($value): ?string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return $value;
    }

    /**
     * Decode value after fetching from database.
     */
    protected static function decode(?string $value)
    {
        if ($value === null) {
            return null;
        }

        $json = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $json : $value;
    }
}
