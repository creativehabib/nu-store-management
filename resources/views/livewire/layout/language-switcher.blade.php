<div>
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="ghost" icon="language" class="hover:bg-zinc-100 dark:hover:bg-zinc-800">
            {{ $currentLocale === 'bn' ? 'বাংলা' : 'English' }}
        </flux:button>
        <flux:menu>
            <flux:menu.item wire:click="changeLanguage('en')" class="{{ $currentLocale === 'en' ? 'font-bold bg-zinc-50 dark:bg-zinc-800' : '' }}">English</flux:menu.item>
            <flux:menu.item wire:click="changeLanguage('bn')" class="{{ $currentLocale === 'bn' ? 'font-bold bg-zinc-50 dark:bg-zinc-800' : '' }}">বাংলা</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
