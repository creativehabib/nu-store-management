<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Support\ApprovalWorkflow;
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
    public ?string $current_logo = null;
    public ?string $current_favicon = null;

    public $show_print_footer;
    public $store_mode;
    public $central_store_dept_id;
    public array $approval_flow_roles = [];

    public function mount(): void
    {
        $this->site_name = setting('site_name', 'Inventory Management System');
        $this->site_email = setting('site_email');
        $this->site_phone = setting('site_phone');
        $this->site_address = setting('site_address');
        $this->facebook_url = setting('facebook_url');
        $this->twitter_url = setting('twitter_url');
        $this->instagram_url = setting('instagram_url');
        $this->current_logo = setting('site_logo');
        $this->current_favicon = setting('site_favicon');

        $this->show_print_footer = (bool) setting('show_print_footer', true);
        // স্টোর মোড লোড করা
        $this->store_mode = setting('store_mode', 'departmental');
        $this->central_store_dept_id = setting('central_store_dept_id', 1);
        $this->approval_flow_roles = ApprovalWorkflow::roles();
    }

    public function addApprovalStep(): void
    {
        $roles = array_filter($this->approval_flow_roles, fn ($role): bool => $role !== 'director');

        foreach (array_keys(ApprovalWorkflow::availableApprovers()) as $role) {
            if ($role !== 'director' && ! in_array($role, $roles, true)) {
                $roles[] = $role;
                break;
            }
        }

        $roles[] = 'director';
        $this->approval_flow_roles = ApprovalWorkflow::rolesFromSelection(array_values($roles));
    }

    public function removeApprovalStep(int $index): void
    {
        unset($this->approval_flow_roles[$index]);

        $this->approval_flow_roles = ApprovalWorkflow::rolesFromSelection(array_values($this->approval_flow_roles));
    }

    public function updatedApprovalFlowRoles(): void
    {
        $this->approval_flow_roles = ApprovalWorkflow::rolesFromSelection($this->approval_flow_roles);
    }

    public function save(): void
    {
        $this->approval_flow_roles = ApprovalWorkflow::rolesFromSelection($this->approval_flow_roles);

        $this->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'nullable|email',
            'logo' => 'nullable|image|max:1024',
            'favicon' => [
                'nullable', 'file', 'max:512',
                'mimes:ico,png,jpg,jpeg,webp,gif,svg',
            ],
            'show_print_footer' => 'boolean',
            // নতুন ভ্যালিডেশন
            'store_mode' => 'required|in:departmental,centralized',
            'central_store_dept_id' => 'required_if:store_mode,centralized|nullable|integer',
            'approval_flow_roles' => 'required|array|min:1',
            'approval_flow_roles.*' => 'in:assistant_director,deputy_director,director',
        ]);

        if ($this->logo) {
            $path = $this->logo->store('settings', 'public');
            set_setting('site_logo', $path);
            $this->current_logo = $path;
        }

        if ($this->favicon) {
            $path = $this->favicon->store('settings', 'public');
            set_setting('site_favicon', $path);
            $this->current_favicon = $path;
        }

        set_setting('site_name', $this->site_name);
        set_setting('site_email', $this->site_email);
        set_setting('site_phone', $this->site_phone);
        set_setting('site_address', $this->site_address);
        set_setting('facebook_url', $this->facebook_url);
        set_setting('twitter_url', $this->twitter_url);
        set_setting('instagram_url', $this->instagram_url);

        set_setting('show_print_footer', $this->show_print_footer);
        // নতুন সেটিংস সেভ করা
        set_setting('store_mode', $this->store_mode);
        set_setting('central_store_dept_id', $this->central_store_dept_id);
        set_setting('approval_flow_roles', ApprovalWorkflow::rolesFromSelection($this->approval_flow_roles));

        $this->reset('logo', 'favicon');

        Flux::toast(__('General settings updated successfully!'));
    }

    public function render(): View
    {
        return view('livewire.admin.general-settings', [
            'departments' => Department::orderBy('name')->get(),
            'approvalApprovers' => ApprovalWorkflow::availableApprovers(),
        ]);
    }
}
