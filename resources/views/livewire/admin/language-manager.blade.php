<div class="max-w-7xl mx-auto space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:card class="flex items-center gap-4 py-4 bg-white dark:bg-zinc-800">
            <div class="p-3 bg-zinc-100 dark:bg-zinc-700 rounded-lg text-zinc-600 dark:text-zinc-300">
                <flux:icon.key class="w-6 h-6" />
            </div>
            <div>
                <flux:heading size="sm" class="text-zinc-500">{{ __('Total Keys') }}</flux:heading>
                <div class="text-2xl font-bold mt-1">{{ $totalKeys }} {{ __('items') }}</div>
            </div>
        </flux:card>

        <flux:card class="flex items-center gap-4 py-4 bg-white dark:bg-zinc-800">
            <div class="p-3 bg-green-50 dark:bg-green-950/30 rounded-lg text-green-600 dark:text-green-400">
                <flux:icon.check-circle class="w-6 h-6" />
            </div>
            <div>
                <flux:heading size="sm" class="text-zinc-500">{{ __('Translated') }}</flux:heading>
                <div class="text-2xl font-bold mt-1 text-green-600 dark:text-green-400">{{ $translatedKeys }} {{ __('items') }}</div>
            </div>
        </flux:card>

        <flux:card class="flex items-center gap-4 py-4 bg-white dark:bg-zinc-800">
            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-lg text-amber-600 dark:text-amber-400">
                <flux:icon.exclamation-circle class="w-6 h-6" />
            </div>
            <div>
                <flux:heading size="sm" class="text-zinc-500">{{ __('Missing Translation') }}</flux:heading>
                <div class="text-2xl font-bold mt-1 text-amber-600 dark:text-amber-400">{{ $missingKeys }} {{ __('items') }}</div>
            </div>
        </flux:card>
    </div>

    <flux:card class="bg-white dark:bg-zinc-800">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-1/2">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search translations...')" class="w-full" />
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <flux:button variant="outline" icon="arrow-path" wire:click="scanCodebase" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="scanCodebase">{{ __('Scan Codebase') }}</span>
                    <span wire:loading wire:target="scanCodebase">{{ __('Scanning...') }}</span>
                </flux:button>

                <div class="w-32">
                    <flux:select wire:model.live="locale">
                        <flux:select.option value="bn">{{ __('BN - Bengali') }}</flux:select.option>
                        <flux:select.option value="en">{{ __('EN - English') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:modal.trigger name="add-translation-modal">
                    <flux:button variant="primary" icon="plus">{{ __('Add New') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </div>
    </flux:card>

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-900 border-b dark:border-zinc-700 text-zinc-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-semibold w-1/4">{{ __('Key') }}</th>
                    <th class="p-4 font-semibold w-1/3">{{ __('EN (English)') }}</th>
                    <th class="p-4 font-semibold w-1/3">{{ strtoupper($locale) }} {{ __('(Translation)') }}</th>
                    <th class="p-4 font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($filteredTranslations as $key => $value)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="p-4 text-sm text-zinc-600 dark:text-zinc-400 font-mono break-all">
                            {{ $key }}
                        </td>

                        <td class="p-4 text-sm">
                            <span class="cursor-pointer text-blue-600 dark:text-blue-400 border-b border-dashed border-blue-400/50 hover:border-blue-600 hover:text-blue-800 dark:hover:text-blue-300 transition-colors" wire:click="editKey('{{ $key }}')">
                                {{ !empty($baseTranslations[$key]) ? $baseTranslations[$key] : __('Empty') }}
                            </span>
                        </td>

                        <td class="p-4 text-sm">
                            <span class="cursor-pointer text-blue-600 dark:text-blue-400 border-b border-dashed border-blue-400/50 hover:border-blue-600 hover:text-blue-800 dark:hover:text-blue-300 transition-colors {{ empty($value) ? 'text-amber-600 dark:text-amber-500 font-medium' : '' }}" wire:click="editKey('{{ $key }}')">
                                {{ !empty($value) ? $value : __('Missing (Empty)') }}
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            <flux:button size="sm" variant="subtle" icon="trash" class="text-red-500 hover:text-red-600 hover:bg-red-50" wire:click="deleteTranslation('{{ $key }}')" wire:confirm="{{ __('Are you sure you want to delete this key?') }}"></flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-zinc-500">
                            {{ __('No translations found.') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    <flux:modal name="edit-translation-modal" class="md:w-1/3">
        <form wire:submit="saveSingleKey" class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Quick Edit') }}</flux:heading>
                <p class="text-xs text-zinc-500 mt-1 break-all">{{ __('Key:') }} <span class="font-mono bg-zinc-100 dark:bg-zinc-800 p-1 rounded">{{ $editingKey }}</span></p>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:textarea wire:model="editEnValue" :label="__('English (EN)')" rows="2" />
                <flux:textarea wire:model="editTargetValue" :label="__('Translation') . ' (' . strtoupper($locale) . ')'" rows="2" />
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark"></flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="add-translation-modal" class="md:w-1/2">
        <form wire:submit="addTranslation" class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Add New Translation') }}</flux:heading>
            </div>
            <flux:separator />
            <flux:input wire:model="newKey" :label="__('Key (English Text)')" :placeholder="__('e.g. Total Users')" required />
            <flux:textarea wire:model="newValue" :label="__('Translation') . ' (' . strtoupper($locale) . ')'" :placeholder="__('e.g. Translated Text')" rows="2" required />

            <div class="flex justify-end gap-2 mt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="plus">{{ __('Add') }}</flux:button>
            </div>
        </form>
    </flux:modal>

</div>
