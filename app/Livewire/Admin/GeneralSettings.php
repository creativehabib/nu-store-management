<?php

namespace App\Livewire\Admin;

use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public $site_name, $site_email, $site_phone, $site_address;
    public $facebook_url, $twitter_url, $instagram_url;
    public $logo, $favicon;

    public function mount(): void
    {
        $this->site_name = setting('site_name', 'Inventory Management System');
        $this->site_email = setting('site_email');
        $this->site_phone = setting('site_phone');
        $this->site_address = setting('site_address');
        $this->facebook_url = setting('facebook_url');
        $this->twitter_url = setting('twitter_url');
        $this->instagram_url = setting('instagram_url');
    }

    public function save(): void
    {
        $this->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'nullable|email',
            'logo' => 'nullable|image|max:1024',
            'favicon' => [
                'nullable',
                'file',
                'max:512',
                'mimes:ico,png,jpg,jpeg,webp,gif,svg',
                'mimetypes:image/x-icon,image/vnd.microsoft.icon,image/png,image/jpeg,image/webp,image/gif,image/svg+xml',
            ],
        ]);

        if ($this->logo) {
            $path = $this->logo->store('settings', 'public');
            set_setting('site_logo', $path);
        }

        if ($this->favicon) {
            $path = $this->favicon->store('settings', 'public');
            set_setting('site_favicon', $path);
        }

        set_setting('site_name', $this->site_name);
        set_setting('site_email', $this->site_email);
        set_setting('site_phone', $this->site_phone);
        set_setting('site_address', $this->site_address);
        set_setting('facebook_url', $this->facebook_url);
        set_setting('twitter_url', $this->twitter_url);
        set_setting('instagram_url', $this->instagram_url);

        Flux::toast(__('General settings updated successfully!'));
    }

    public function render(): View
    {
        return view('livewire.admin.general-settings');
    }
}
