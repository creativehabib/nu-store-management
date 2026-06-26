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
        // ১. ভ্যালিডেশন যুক্ত করা হলো
        $this->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
        ]);

        $data = [
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->mail_password,
            'mail_encryption' => $this->mail_encryption,
            'mail_from_address' => $this->mail_from_address,
        ];

        // ২. ডাটা সেভ এবং ক্যাশ ক্লিয়ার করা
        foreach ($data as $key => $value) {
            // যদি আপনি set_setting() হেল্পার ফাংশনটি ব্যবহার করে থাকেন:
            if (function_exists('set_setting')) {
                set_setting($key, $value);
            } else {
                // হেল্পার না থাকলে ডিফল্ট নিয়ম
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        Flux::toast(__('Mail settings updated successfully!'));
    }

    public function render(): Factory|View
    {
        return view('livewire.admin.mail-settings');
    }
}
