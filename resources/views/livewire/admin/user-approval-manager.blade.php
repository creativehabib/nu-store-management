<div class="max-w-7xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">ইউজার ম্যানেজমেন্ট প্যানেল</flux:heading>

    @if($isEditMode)
        <flux:card class="bg-zinc-50 dark:bg-zinc-800 border-indigo-200 dark:border-indigo-800">
            <div class="mb-4 border-b pb-2 flex justify-between items-center">
                <flux:heading size="lg">ইউজারের তথ্য আপডেট করুন</flux:heading>
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="resetFields" />
            </div>

            <form wire:submit="update" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input wire:model="name" label="নাম" required />
                    <flux:input wire:model="pf_no" label="PF No" required />
                    <flux:input wire:model="mobile_no" label="মোবাইল নম্বর" required />
                    <flux:input wire:model="email" label="ইমেইল অ্যাড্রেস" type="email" required />
                    <flux:input wire:model="post" label="পদবি" required />
                    <flux:input wire:model="department" label="দপ্তর" required />

                    <flux:select wire:model="role" label="সিস্টেম রোল" required>
                        <flux:select.option value="requisitioner">Requisitioner</flux:select.option>
                        <flux:select.option value="initiator">Initiator</flux:select.option>
                        <flux:select.option value="assistant_director">Assistant Director</flux:select.option>
                        <flux:select.option value="deputy_director">Deputy Director</flux:select.option>
                        <flux:select.option value="director">Director</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button type="button" variant="outline" wire:click="resetFields">বাতিল</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">আপডেট করুন</flux:button>
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
                    <th class="p-3 text-sm font-semibold">নাম ও কন্টাক্ট</th>
                    <th class="p-3 text-sm font-semibold">পদবি ও দপ্তর</th>
                    <th class="p-3 text-sm font-semibold">রোল</th>
                    <th class="p-3 text-sm font-semibold">স্ট্যাটাস</th>
                    <th class="p-3 text-sm font-semibold text-right">অ্যাকশন</th>
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
                                <flux:badge color="green">Approved</flux:badge>
                            @else
                                <flux:badge color="amber">Pending / Suspended</flux:badge>
                            @endif
                        </td>

                        <td class="p-3 text-right">
                            <div class="flex justify-end gap-2">

                                @if($user->is_approved)
                                    <flux:button size="sm" variant="danger" icon="no-symbol" wire:click="toggleApproval({{ $user->id }})" wire:confirm="আপনি কি এই ইউজারের অ্যাক্সেস বন্ধ (Unapprove) করতে চান?" title="Unapprove">
                                        Suspend
                                    </flux:button>
                                @else
                                    <flux:button size="sm" variant="primary" icon="check" wire:click="toggleApproval({{ $user->id }})" wire:confirm="আপনি কি এই অ্যাকাউন্টটি অ্যাপ্রুভ করতে চান?" title="Approve">
                                        Approve
                                    </flux:button>
                                @endif

                                <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $user->id }})" title="Edit User" />

                                <flux:button size="sm" variant="outline" icon="trash" wire:click="deleteUser({{ $user->id }})" wire:confirm="এই ইউজারের সম্পূর্ণ ডাটা মুছে ফেলতে চান?" title="Delete User" class="text-red-500 hover:text-red-700" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">সিস্টেমে কোনো ইউজার পাওয়া যায়নি।</p>
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
