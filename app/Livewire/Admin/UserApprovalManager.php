<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Department; // নতুন যুক্ত করা হলো
use App\Models\Designation; // নতুন যুক্ত করা হলো
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class UserApprovalManager extends Component
{
    use WithPagination;

    public $userId;

    public $name;

    public $email;

    public $pf_no;

    public $designation_id; // post এর পরিবর্তে

    public $department_id; // department এর পরিবর্তে

    public $role;

    public $mobile_no;

    public $isEditMode = false;

    // Properties for Modal
    public $targetUserId = null;

    public $actionType = ''; // 'suspend' or 'delete'

    // Open Modal
    public function confirmAction($id, $type)
    {
        $this->targetUserId = $id;
        $this->actionType = $type;
        Flux::modal('delete-user-modal')->show();
    }

    // Execute Action from Modal
    public function executeAction()
    {
        if (! $this->targetUserId || auth()->id() == $this->targetUserId) {
            Flux::toast('This operation is not allowed!', 'danger');

            return;
        }

        if ($this->actionType === 'suspend') {
            $user = User::findOrFail($this->targetUserId);
            $user->update(['is_approved' => ! $user->is_approved]);
            Flux::toast($user->is_approved ? 'User account approved!' : 'User account suspended!');
        } elseif ($this->actionType === 'delete') {
            User::findOrFail($this->targetUserId)->delete();
            Flux::toast('User account deleted successfully!');
        }

        $this->targetUserId = null;
        $this->actionType = '';
        Flux::modal('delete-user-modal')->close();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->pf_no = $user->pf_no;
        $this->designation_id = $user->designation_id; // আপডেট করা হলো
        $this->department_id = $user->department_id;   // আপডেট করা হলো
        $this->role = $user->role;
        $this->mobile_no = $user->mobile_no;
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->userId,
            'pf_no' => 'required|string|unique:users,pf_no,'.$this->userId,
            'designation_id' => 'required|exists:designations,id', // ভ্যালিডেশন রুল আপডেট
            'department_id' => 'required|exists:departments,id',   // ভ্যালিডেশন রুল আপডেট
            'role' => 'required|in:director,deputy_director,assistant_director,initiator,requisitioner,admin',
            'mobile_no' => 'required|string|max:20',
        ]);

        User::findOrFail($this->userId)->update([
            'name' => $this->name,
            'email' => $this->email,
            'pf_no' => $this->pf_no,
            'designation_id' => $this->designation_id, // আপডেট করা হলো
            'department_id' => $this->department_id,   // আপডেট করা হলো
            'role' => $this->role,
            'mobile_no' => $this->mobile_no,
        ]);

        Flux::toast('User information updated successfully!');
        $this->resetFields();
    }

    public function resetFields(): void
    {
        // রিসেট ফিল্ড আপডেট করা হলো
        $this->reset(['userId', 'name', 'email', 'pf_no', 'designation_id', 'department_id', 'role', 'mobile_no', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        // N+1 কুয়েরি সমস্যা সমাধানের জন্য eager loading (with) যুক্ত করা হলো
        $users = User::with(['department', 'designation'])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(10);

        return view('livewire.admin.user-approval-manager', [
            'users' => $users,
            'departments' => Department::orderBy('name')->get(),     // ড্রপডাউনের জন্য
            'designations' => Designation::orderBy('rank')->get(),   // ড্রপডাউনের জন্য
        ])->layout('layouts.app', ['title' => 'User Manager']);
    }
}
