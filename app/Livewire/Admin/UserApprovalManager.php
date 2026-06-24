<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Flux\Flux;

class UserApprovalManager extends Component
{
    use WithPagination;

    // এডিট করার জন্য প্রোপার্টিসমূহ
    public $userId, $name, $email, $pf_no, $post, $department, $role, $mobile_no;
    public $isEditMode = false;

    // অ্যাপ্রুভ এবং আন-অ্যাপ্রুভ টগল করার মেথড
    public function toggleApproval($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_approved' => !$user->is_approved]);

        $msg = $user->is_approved ? 'ইউজার অ্যাকাউন্ট অ্যাপ্রুভ করা হয়েছে!' : 'ইউজার অ্যাকাউন্ট সাসপেন্ড/আন-অ্যাপ্রুভ করা হয়েছে!';
        Flux::toast($msg);
    }

    // এডিট মোড অন করার মেথড
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->pf_no = $user->pf_no;
        $this->post = $user->post;
        $this->department = $user->department;
        $this->role = $user->role;
        $this->mobile_no = $user->mobile_no;

        $this->isEditMode = true;
    }

    // ইউজারের ইনফরমেশন আপডেট করার মেথড
    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'pf_no' => 'required|string|unique:users,pf_no,' . $this->userId,
            'post' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'role' => 'required|in:director,deputy_director,assistant_director,initiator,requisitioner',
            'mobile_no' => 'required|string|max:20',
        ]);

        $user = User::findOrFail($this->userId);
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'pf_no' => $this->pf_no,
            'post' => $this->post,
            'department' => $this->department,
            'role' => $this->role,
            'mobile_no' => $this->mobile_no,
        ]);

        Flux::toast('ইউজারের তথ্য সফলভাবে আপডেট করা হয়েছে!');
        $this->resetFields();
    }

    // ইউজার ডিলিট করার মেথড
    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        Flux::toast('ইউজার অ্যাকাউন্ট মুছে ফেলা হয়েছে!');
    }

    // ফর্ম রিসেট
    public function resetFields()
    {
        $this->reset(['userId', 'name', 'email', 'pf_no', 'post', 'department', 'role', 'mobile_no', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        // এডমিন বাদে সিস্টেমের সকল ইউজারকে আনা হচ্ছে (Approve এবং Unapprove উভয়ই)
        $users = User::where('role', '!=', 'admin')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.user-approval-manager', [
            'users' => $users
        ]);
    }
}
