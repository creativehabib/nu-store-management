<div class="max-w-2xl mx-auto py-6">
    <flux:card>
        <flux:heading size="lg">{{ __('SMTP Mail Settings') }}</flux:heading>
        <flux:subheading>{{ __('Configure your email server settings here.') }}</flux:subheading>

        <form wire:submit="save" class="space-y-4 mt-6">
            <flux:input wire:model="mail_host" label="{{ __('Mail Host') }}" placeholder="smtp.example.com" />
            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="mail_port" label="{{ __('Port') }}" placeholder="587" />
                <flux:input wire:model="mail_encryption" label="{{ __('Encryption') }}" placeholder="tls" />
            </div>
            <flux:input wire:model="mail_username" label="{{ __('Username') }}" />
            <flux:input wire:model="mail_password" label="{{ __('Password') }}" type="password" viewable />
            <flux:input wire:model="mail_from_address" label="{{ __('From Address') }}" placeholder="noreply@example.com" />

            <div class="flex justify-end mt-4">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
