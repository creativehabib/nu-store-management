<div class="max-w-7xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('User Management Panel') }}</flux:heading>

    @if($isEditMode)
        <flux:card class="bg-zinc-50 dark:bg-zinc-800 border-indigo-200 dark:border-indigo-800">
            <div class="mb-4 border-b pb-2 flex justify-between items-center">
                <flux:heading size="lg">{{ __('Update user information.') }}</flux:heading>
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="resetFields" />
            </div>

            <form wire:submit="update" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input wire:model="name" :label="__('Name')" required />
                    <flux:input wire:model="pf_no" :label="__('PF No')" required />
                    <flux:input wire:model="mobile_no" :label="__('Mobile No')" required />
                    <flux:input wire:model="email" :label="__('Email')" type="email" required />
                    <flux:input wire:model="post" :label="__('Designation')" required />
                    <flux:input wire:model="department" :label="__('Department')" required />

                    <flux:select wire:model="role" :label="__('System Role')" required>
                        <flux:select.option value="requisitioner">{{ __('Requisitioner') }}</flux:select.option>
                        <flux:select.option value="initiator">{{ __('Initiator') }}</flux:select.option>
                        <flux:select.option value="assistant_director">{{ __('Assistant Director') }}</flux:select.option>
                        <flux:select.option value="deputy_director">{{ __('Deputy Director') }}</flux:select.option>
                        <flux:select.option value="director">{{ __('Director') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button type="button" variant="outline" wire:click="resetFields">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ __('Update') }}</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">{{ __('PF No') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Name & Contact') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Designation & Department') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Role') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Status') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-bold text-zinc-700 dark:text-zinc-300">{{ $user->pf_no }}</td>

                        <td class="p-3">
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500 mt-1">{{ $user->email }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->mobile_no }}</div>
                        </td>

                        <td class="p-3">
                            <div class="font-medium">{{ $user->post }}</div>
                            <div class="text-xs text-zinc-500 mt-1">{{ $user->department }}</div>
                        </td>

                        <td class="p-3">
                            <flux:badge color="zinc">
                                {{ ucwords(str_replace('_', ' ', $user->role)) }}
                            </flux:badge>
                        </td>

                        <td class="p-3">
                            @if($user->is_approved)
                                <flux:badge color="green">{{ __('Approved') }}</flux:badge>
                            @else
                                <flux:badge color="amber">{{ __('Pending/Suspended') }}</flux:badge>
                            @endif
                        </td>

                        <td class="p-3 text-right">
                            <div class="flex justify-end gap-2">

                                @if($user->is_approved)
                                    <flux:button size="sm" variant="danger" icon="no-symbol" wire:click="toggleApproval({{ $user->id }})" wire:confirm="{{ __('Do you want to revoke this user\'s access (unapprove)?') }}" title="{{ __('Unapprove') }}">
                                        {{ __('Suspend') }}
                                    </flux:button>
                                @else
                                    <flux:button size="sm" variant="primary" icon="check" wire:click="toggleApproval({{ $user->id }})" wire:confirm="{{ __('Do you want to approve this account?') }}" title="{{ __('Approve') }}">
                                        {{ __('Approve') }}
                                    </flux:button>
                                @endif

                                <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $user->id }})" title="{{ __('Edit User') }}" />

                                <flux:button size="sm" variant="outline" icon="trash" wire:click="deleteUser({{ $user->id }})" wire:confirm="{{ __('Do you want to delete all data for this user?') }}" title="{{ __('Delete User') }}" class="text-red-500 hover:text-red-700" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">{{ __('No users found in the system.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </flux:card>
</div>
