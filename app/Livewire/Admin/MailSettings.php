<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MailSettings extends Component
{
    public bool $mail_enabled = true;

    public string $mail_host = '';

    public string|int $mail_port = '';

    public ?string $mail_username = '';

    public ?string $mail_password = '';

    public ?string $mail_encryption = 'tls';

    public string $mail_from_address = '';

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key');
        $this->mail_enabled = filter_var($settings['mail_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $this->mail_host = $settings['mail_host'] ?? '';
        $this->mail_port = $settings['mail_port'] ?? '';
        $this->mail_username = $settings['mail_username'] ?? '';
        $this->mail_password = $settings['mail_password'] ?? '';
        $this->mail_encryption = $settings['mail_encryption'] ?? 'tls';
        $this->mail_from_address = $settings['mail_from_address'] ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'mail_enabled' => ['boolean'],
            'mail_host' => [Rule::requiredIf($this->mail_enabled), 'nullable', 'string', 'max:255'],
            'mail_port' => [Rule::requiredIf($this->mail_enabled), 'nullable', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'starttls', 'smtp', 'smtps'])],
            'mail_from_address' => [Rule::requiredIf($this->mail_enabled), 'nullable', 'email:rfc'],
        ]);

        $data = [
            'mail_enabled' => $this->mail_enabled ? '1' : '0',
            'mail_host' => $this->mail_host,
            'mail_port' => (string) $this->mail_port,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->mail_password,
            'mail_encryption' => $this->mail_encryption,
            'mail_from_address' => $this->mail_from_address,
        ];

        foreach ($data as $key => $value) {
            if (function_exists('set_setting')) {
                set_setting($key, $value, 'mail');
            } else {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'mail', 'autoload' => true],
                );
            }
        }

        if (! $this->mail_enabled) {
            config(['mail.default' => 'log']);

            Flux::toast(__('Mail settings updated successfully!'));

            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => match ($this->mail_encryption) {
                'ssl', 'smtps' => 'smtps',
                'tls', 'starttls', 'smtp' => 'smtp',
                default => null,
            },
            'mail.mailers.smtp.host' => $this->mail_host,
            'mail.mailers.smtp.port' => (int) $this->mail_port,
            'mail.mailers.smtp.username' => $this->mail_username,
            'mail.mailers.smtp.password' => $this->mail_password,
            'mail.from.address' => $this->mail_from_address,
        ]);

        Flux::toast(__('Mail settings updated successfully!'));
    }

    public function render(): Factory|View
    {
        return view('livewire.admin.mail-settings');
    }
}
