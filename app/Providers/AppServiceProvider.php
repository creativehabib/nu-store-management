<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Setting;
use App\Observers\ProductObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Product::observe(ProductObserver::class);

        // মেইল সেটিংস ডাটাবেস থেকে লোড করা
        $this->loadMailSettings();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Load mail settings from database.
     */
    protected function loadMailSettings(): void
    {
        // নিশ্চিত করুন যে টেবিলটি বিদ্যমান
        if (Schema::hasTable('settings')) {
            $settings = Setting::pluck('value', 'key');

            if ($settings->isNotEmpty()) {
                config(['mail.mailers.smtp.host' => $settings['mail_host'] ?? env('MAIL_HOST')]);
                config(['mail.mailers.smtp.port' => $settings['mail_port'] ?? env('MAIL_PORT')]);
                config(['mail.mailers.smtp.username' => $settings['mail_username'] ?? env('MAIL_USERNAME')]);
                config(['mail.mailers.smtp.password' => $settings['mail_password'] ?? env('MAIL_PASSWORD')]);
                config(['mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? env('MAIL_ENCRYPTION')]);
                config(['mail.from.address' => $settings['mail_from_address'] ?? env('MAIL_FROM_ADDRESS')]);
            }
        }
    }
}
