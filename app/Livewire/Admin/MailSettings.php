<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MailSettings extends Component
{
    public $mail_host;

    public $mail_port;

    public $mail_username;

    public $mail_password;

    public $mail_encryption;

    public $mail_from_address;

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key');
        $this->mail_host = $settings['mail_host'] ?? '';
        $this->mail_port = $settings['mail_port'] ?? '';
        $this->mail_username = $settings['mail_username'] ?? '';
        $this->mail_password = $settings['mail_password'] ?? '';
        $this->mail_encryption = $settings['mail_encryption'] ?? 'tls';
        $this->mail_from_address = $settings['mail_from_address'] ?? '';
    }

    public function save(): void
    {
        $data = [
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->mail_password,
            'mail_encryption' => $this->mail_encryption,
            'mail_from_address' => $this->mail_from_address,
        ];

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Flux::toast(__('Mail settings updated successfully!'));
    }

    public function render(): Factory|View
    {
        return view('livewire.admin.mail-settings');
    }
}
