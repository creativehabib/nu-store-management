<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col gap-2 border-b pb-2 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('User Management Panel') }}</flux:heading>
            <flux:subheading>{{ __('Manage approvals, roles, departments, profile images, and signatures from one place.') }}</flux:subheading>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <flux:card class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
            <flux:text size="sm" class="text-blue-600 dark:text-blue-400">{{ __('Total Users') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $totalUsers }}</flux:heading>
        </flux:card>
        <flux:card class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
            <flux:text size="sm" class="text-green-600 dark:text-green-400">{{ __('Approved') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $approvedUsers }}</flux:heading>
        </flux:card>
        <flux:card class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
            <flux:text size="sm" class="text-amber-600 dark:text-amber-400">{{ __('Pending') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $pendingUsers }}</flux:heading>
        </flux:card>
        <flux:card class="border-indigo-200 bg-indigo-50 dark:border-indigo-800 dark:bg-indigo-900/20">
            <flux:text size="sm" class="text-indigo-600 dark:text-indigo-400">{{ __('Admins') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $adminUsers }}</flux:heading>
        </flux:card>
    </div>

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
        <div class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_180px_auto] lg:items-end">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                :label="__('Search Users')"
                placeholder="{{ __('Name, email, PF, mobile, department, or designation...') }}"
            />

            <flux:select wire:model.live="roleFilter" :label="__('Role')">
                <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                <flux:select.option value="requisitioner">{{ __('Requisitioner') }}</flux:select.option>
                <flux:select.option value="initiator">{{ __('Initiator') }}</flux:select.option>
                <flux:select.option value="assistant_director">{{ __('Assistant Director') }}</flux:select.option>
                <flux:select.option value="deputy_director">{{ __('Deputy Director') }}</flux:select.option>
                <flux:select.option value="director">{{ __('Director') }}</flux:select.option>
                <flux:select.option value="admin">{{ __('Admin') }}</flux:select.option>
                <flux:select.option value="super_admin">{{ __('Super Admin') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="approved">{{ __('Approved') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
            </flux:select>

            <flux:button type="button" variant="outline" icon="x-mark" wire:click="clearFilters">
                {{ __('Clear') }}
            </flux:button>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-zinc-100 text-xs uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="p-3 font-semibold">{{ __('User') }}</th>
                        <th class="p-3 font-semibold">{{ __('PF & Mobile') }}</th>
                        <th class="p-3 font-semibold">{{ __('Designation / Department') }}</th>
                        <th class="p-3 font-semibold">{{ __('Role') }}</th>
                        <th class="p-3 font-semibold">{{ __('Status') }}</th>
                        <th class="p-3 font-semibold text-right">{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($users as $user)
                        <tr class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/70" wire:key="managed-user-{{ $user->id }}">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-100 font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        @if($user->picture)
                                            <img src="{{ asset('storage/' . $user->picture) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $user->initials() }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</div>
                                        <div class="truncate text-xs text-zinc-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->pf_no }}</div>
                                <div class="text-xs text-zinc-500">{{ $user->mobile_no }}</div>
                            </td>

                            <td class="p-3">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->designation->title ?? 'N/A' }}</div>
                                <div class="text-xs text-zinc-500">{{ $user->department->name ?? 'N/A' }}</div>
                            </td>

                            <td class="p-3">
                                <flux:badge color="zinc">{{ ucwords(str_replace('_', ' ', $user->role)) }}</flux:badge>
                            </td>

                            <td class="p-3">
                                <flux:badge color="{{ $user->is_approved ? 'green' : 'amber' }}">{{ $user->is_approved ? __('Approved') : __('Pending') }}</flux:badge>
                            </td>

                            <td class="p-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $user->id }})" title="{{ __('Edit') }}" />

                                    @if($user->id !== auth()->id())
                                        <flux:button size="sm" variant="{{ $user->is_approved ? 'danger' : 'primary' }}"
                                                     wire:click="confirmAction({{ $user->id }}, 'suspend')">
                                            {{ $user->is_approved ? __('Suspend') : __('Approve') }}
                                        </flux:button>

                                        <flux:button size="sm" variant="outline" icon="trash"
                                                     wire:click="confirmAction({{ $user->id }}, 'delete')"
                                                     class="text-red-500 hover:text-red-700" />
                                    @else
                                        <flux:badge color="zinc">{{ __('You (Admin)') }}</flux:badge>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-zinc-500">
                                <p class="text-lg font-medium">{{ __('No users found.') }}</p>
                                <p class="mt-1 text-sm">{{ __('Try clearing search or filters.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
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
