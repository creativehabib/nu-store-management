<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\Setting;
use App\Observers\ProductObserver;
use App\Observers\RequisitionObserver;
use App\Support\WorkflowQueueCounter;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Requisition::observe(RequisitionObserver::class);
        Event::listen(Login::class, LogSuccessfulLogin::class);

        // মেইল সেটিংস ডাটাবেস থেকে লোড করা
        $this->loadMailSettings();
        $this->shareWorkflowQueueCounts();
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
            ? Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Load SMTP mail settings from the database when the application is installed.
     */
    protected function loadMailSettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $settings = Setting::query()
            ->whereIn('key', [
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'mail_from_address',
                'mail_enabled',
            ])
            ->pluck('value', 'key');

        if (! $this->mailEnabled($settings->get('mail_enabled', '1'))) {
            config(['mail.default' => 'log']);

            return;
        }

        if (! $settings->has('mail_host') || ! $settings->has('mail_from_address')) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $this->mailScheme($settings->get('mail_encryption')),
            'mail.mailers.smtp.host' => $settings->get('mail_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => (int) $settings->get('mail_port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.username' => $settings->get('mail_username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => $settings->get('mail_password', config('mail.mailers.smtp.password')),
            'mail.from.address' => $settings->get('mail_from_address', config('mail.from.address')),
        ]);
    }

    protected function shareWorkflowQueueCounts(): void
    {
        View::composer(
            ['layouts.app.sidebar', 'layouts::app.sidebar', 'components.layouts.app.sidebar'],
            function ($view): void {
                $view->with(
                    'workflowQueueCounts',
                    app(WorkflowQueueCounter::class)->countsFor(Auth::user()),
                );
            },
        );
    }

    /**
     * Determine whether real email delivery is enabled.
     */
    protected function mailEnabled(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Convert legacy encryption values to Laravel's SMTP scheme option.
     */
    protected function mailScheme(?string $encryption): ?string
    {
        return match (strtolower((string) $encryption)) {
            'ssl', 'smtps' => 'smtps',
            'tls', 'starttls', 'smtp' => 'smtp',
            default => null,
        };
    }
}
