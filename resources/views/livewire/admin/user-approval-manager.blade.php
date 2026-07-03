<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('User Management Panel') }}</flux:heading>

    @if($isEditMode)
        <flux:card class="bg-zinc-50 dark:bg-zinc-800 border-indigo-200 dark:border-indigo-800">
            <div class="mb-4 border-b pb-2 flex justify-between items-center">
                <flux:heading size="lg">{{ __('Update user information') }}</flux:heading>
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="resetFields" />
            </div>
            <form wire:submit="update" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input wire:model="name" label="Name" required />
                    <flux:input wire:model="pf_no" label="PF No" required />
                    <flux:input wire:model="mobile_no" label="Mobile No" required />
                    <flux:input wire:model="email" label="Email" type="email" required />
                    <flux:input wire:model="password" label="New Password" type="password" autocomplete="new-password" viewable />
                    <flux:input wire:model="password_confirmation" label="Confirm Password" type="password" autocomplete="new-password" viewable />
                    <flux:input wire:model="picture" label="Profile Image" type="file" accept="image/*" />
                    <flux:input wire:model="digital_signature" label="Digital Signature" type="file" accept="image/*" />

                    <flux:select wire:model="designation_id" label="Designation" required>
                        <flux:select.option value="">{{ __('Select Designation') }}</flux:select.option>
                        @foreach($designations as $desig)
                            <flux:select.option value="{{ $desig->id }}">{{ $desig->title }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="department_id" label="Department" required>
                        <flux:select.option value="">{{ __('Select Department') }}</flux:select.option>
                        @foreach($departments as $dept)
                            <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="role" label="System Role" required>
                        <flux:select.option value="requisitioner">Requisitioner</flux:select.option>
                        <flux:select.option value="initiator">Initiator</flux:select.option>
                        <flux:select.option value="assistant_director">Assistant Director</flux:select.option>
                        <flux:select.option value="deputy_director">Deputy Director</flux:select.option>
                        <flux:select.option value="director">Director</flux:select.option>
                        <flux:select.option value="admin">Admin</flux:select.option>
                        <flux:select.option value="super_admin">Super Admin</flux:select.option>
                    </flux:select>
                </div>

                @if($current_picture || $current_signature)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                        @if($current_picture)
                            <div>
                                <p class="text-sm font-medium mb-2 text-zinc-700 dark:text-zinc-300">{{ __('Current Profile Image') }}</p>
                                <img src="{{ asset('storage/' . $current_picture) }}" alt="Profile image" class="h-20 w-20 rounded-full object-cover">
                            </div>
                        @endif

                        @if($current_signature)
                            <div>
                                <p class="text-sm font-medium mb-2 text-zinc-700 dark:text-zinc-300">{{ __('Current Signature') }}</p>
                                <div class="inline-block rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700">
                                    <img src="{{ asset('storage/' . $current_signature) }}" alt="Signature" class="h-16 object-contain">
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button type="button" variant="outline" wire:click="resetFields">Cancel</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">Update</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">PF No</th>
                    <th class="p-3 text-sm font-semibold">Name & Contact</th>
                    <th class="p-3 text-sm font-semibold">Designation</th>
                    <th class="p-3 text-sm font-semibold">Role</th>
                    <th class="p-3 text-sm font-semibold">Status</th>
                    <th class="p-3 text-sm font-semibold text-right">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-bold">{{ $user->pf_no }}</td>
                        <td class="p-3">
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->email }} / {{ $user->mobile_no }}</div>
                        </td>
                        <td class="p-3 text-sm">
                            {{ $user->designation->title ?? 'N/A' }}<br>
                            <span class="text-xs text-zinc-500">{{ $user->department->name ?? 'N/A' }}</span>
                        </td>
                        <td class="p-3"><flux:badge color="zinc">{{ ucwords(str_replace('_', ' ', $user->role)) }}</flux:badge></td>
                        <td class="p-3"><flux:badge color="{{ $user->is_approved ? 'green' : 'amber' }}">{{ $user->is_approved ? 'Approved' : 'Pending' }}</flux:badge></td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $user->id }})" title="Edit" />

                                @if($user->id !== auth()->id())
                                    <flux:button size="sm" variant="{{ $user->is_approved ? 'danger' : 'primary' }}"
                                                 wire:click="confirmAction({{ $user->id }}, 'suspend')">
                                        {{ $user->is_approved ? 'Suspend' : 'Approve' }}
                                    </flux:button>

                                    <flux:button size="sm" variant="outline" icon="trash"
                                                 wire:click="confirmAction({{ $user->id }}, 'delete')"
                                                 class="text-red-500 hover:text-red-700" />
                                @else
                                    <flux:badge color="zinc">You (Admin)</flux:badge>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-zinc-500">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </flux:card>

    <x-delete-modal
        name="delete-user-modal"
        action="executeAction"
        buttonText="{{ $actionType === 'delete' ? 'Delete' : 'Confirm' }}"
        title="{{ $actionType === 'delete' ? 'Delete User?' : 'Change Status?' }}"
        description="{{ __('Are you sure you want to proceed with this operation? This will take effect immediately.') }}"
    />
</div>
