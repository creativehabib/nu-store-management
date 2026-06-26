<?php

namespace App\Support;

class CacheSettings
{
    public static function cacheLifetimeMinutes(): int
    {
        return max(0, (int) setting('cache_time', 60));
    }

    public static function sitemapLifetimeMinutes(): int
    {
        return max(0, (int) setting('sitemap_cache_time', 60));
    }

    public static function cacheWidgetsEnabled(): bool
    {
        return (bool) setting('cache_widgets', true);
    }

    public static function cacheFrontMenusEnabled(): bool
    {
        return (bool) setting('cache_front_menus', true);
    }

    public static function cacheAdminMenuEnabled(): bool
    {
        return (bool) setting('cache_admin_menu', true);
    }

    public static function cacheUserAvatarEnabled(): bool
    {
        return (bool) setting('cache_user_avatar', false);
    }

    public static function cacheShortcodesEnabled(): bool
    {
        return (bool) setting('cache_shortcodes', true);
    }

    public static function resetOnContentChange(): bool
    {
        return (bool) setting('reset_cache_on_data_change', true);
    }
}
