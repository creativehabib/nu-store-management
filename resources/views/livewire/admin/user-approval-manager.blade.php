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
                    <flux:input wire:model="post" label="Designation" required />
                    <flux:input wire:model="department" label="Department" required />
                    <flux:select wire:model="role" label="System Role" required>
                        <flux:select.option value="requisitioner">Requisitioner</flux:select.option>
                        <flux:select.option value="initiator">Initiator</flux:select.option>
                        <flux:select.option value="assistant_director">Assistant Director</flux:select.option>
                        <flux:select.option value="deputy_director">Deputy Director</flux:select.option>
                        <flux:select.option value="director">Director</flux:select.option>
                        <flux:select.option value="admin">Admin</flux:select.option>
                    </flux:select>
                </div>
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
                        <td class="p-3 text-sm">{{ $user->post }}<br><span class="text-xs text-zinc-500">{{ $user->department }}</span></td>
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
