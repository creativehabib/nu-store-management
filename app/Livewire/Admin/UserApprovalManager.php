<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class UserApprovalManager extends Component
{
    use WithFileUploads, WithPagination;

    public $userId;

    public $name;

    public $email;

    public $pf_no;

    public $designation_id; // post এর পরিবর্তে

    public $department_id; // department এর পরিবর্তে

    public $role;

    public $mobile_no;

    public string $password = '';

    public string $password_confirmation = '';

    public $picture;

    public $digital_signature;

    public ?string $current_picture = null;

    public ?string $current_signature = null;

    public $isEditMode = false;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'roleFilter', 'statusFilter']);
        $this->resetPage();
    }

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
        $this->current_picture = $user->picture;
        $this->current_signature = $user->digital_signature;
        $this->reset('password', 'password_confirmation', 'picture', 'digital_signature');
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
            'role' => 'required|in:director,deputy_director,assistant_director,initiator,requisitioner,admin,super_admin',
            'mobile_no' => 'required|string|max:20',
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'picture' => ['nullable', 'image', 'max:2048'],
            'digital_signature' => ['nullable', 'image', 'max:2048'],
        ]);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'pf_no' => $this->pf_no,
            'designation_id' => $this->designation_id, // আপডেট করা হলো
            'department_id' => $this->department_id,   // আপডেট করা হলো
            'role' => $this->role,
            'mobile_no' => $this->mobile_no,
        ];

        if (filled($this->password)) {
            $userData['password'] = $this->password;
        }

        $user = User::findOrFail($this->userId);

        if ($this->picture) {
            $this->deletePublicFile($user->picture);
            $userData['picture'] = $this->picture->store('profile-images', 'public');
        }

        if ($this->digital_signature) {
            $this->deletePublicFile($user->digital_signature);
            $userData['digital_signature'] = $this->digital_signature->store('signatures', 'public');
        }

        $user->update($userData);

        Flux::toast('User information updated successfully!');
        $this->resetFields();
    }

    public function resetFields(): void
    {
        // রিসেট ফিল্ড আপডেট করা হলো
        $this->reset([
            'userId',
            'name',
            'email',
            'pf_no',
            'designation_id',
            'department_id',
            'role',
            'mobile_no',
            'password',
            'password_confirmation',
            'picture',
            'digital_signature',
            'current_picture',
            'current_signature',
            'isEditMode',
        ]);
        $this->resetValidation();
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function render()
    {
        // N+1 কুয়েরি সমস্যা সমাধানের জন্য eager loading (with) যুক্ত করা হলো
        $users = User::with(['department', 'designation'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($userQuery) {
                    $userQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('pf_no', 'like', '%'.$this->search.'%')
                        ->orWhere('mobile_no', 'like', '%'.$this->search.'%')
                        ->orWhereHas('department', function ($departmentQuery) {
                            $departmentQuery->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('designation', function ($designationQuery) {
                            $designationQuery->where('title', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->roleFilter !== '', function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_approved', $this->statusFilter === 'approved');
            })
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(10);

        return view('livewire.admin.user-approval-manager', [
            'users' => $users,
            'departments' => Department::orderBy('name')->get(),     // ড্রপডাউনের জন্য
            'designations' => Designation::orderBy('rank')->get(),   // ড্রপডাউনের জন্য
            'totalUsers' => User::count(),
            'approvedUsers' => User::where('is_approved', true)->count(),
            'pendingUsers' => User::where('is_approved', false)->count(),
            'adminUsers' => User::whereIn('role', ['admin', 'super_admin'])->count(),
        ])->layout('layouts.app', ['title' => 'User Manager']);
    }
}
