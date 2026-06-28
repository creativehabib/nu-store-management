<?php

use App\Livewire\Admin\MailSettings;
use App\Models\Setting;
use App\Providers\AppServiceProvider;
use Livewire\Livewire;

it('loads saved smtp settings into the active mail configuration', function () {
    Setting::insert([
        [
            'key' => 'mail_host',
            'value' => 'smtp.example.com',
            'group' => 'mail',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'mail_port',
            'value' => '465',
            'group' => 'mail',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'mail_username',
            'value' => 'store@example.com',
            'group' => 'mail',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'mail_password',
            'value' => 'secret',
            'group' => 'mail',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'mail_encryption',
            'value' => 'ssl',
            'group' => 'mail',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'mail_from_address',
            'value' => 'noreply@example.com',
            'group' => 'mail',
            'autoload' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    app(AppServiceProvider::class)->boot();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.mailers.smtp.port'))->toBe(465)
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.from.address'))->toBe('noreply@example.com');
});

it('saves mail settings in the mail group and refreshes the active configuration', function () {
    Livewire::test(MailSettings::class)
        ->set('mail_host', 'smtp.mailtrap.io')
        ->set('mail_port', 587)
        ->set('mail_username', 'user')
        ->set('mail_password', 'password')
        ->set('mail_encryption', 'tls')
        ->set('mail_from_address', 'admin@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::where('key', 'mail_host')->value('group'))->toBe('mail')
        ->and(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.mailtrap.io')
        ->and(config('mail.mailers.smtp.port'))->toBe(587)
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtp')
        ->and(config('mail.from.address'))->toBe('admin@example.com');
});
