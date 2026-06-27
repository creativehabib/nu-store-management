<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <flux:card>
        <flux:heading size="lg">{{ __('General Settings') }}</flux:heading>
        <flux:subheading>{{ __('Manage your website identity and contact information.') }}</flux:subheading>

        <form wire:submit="save" class="space-y-6 mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input wire:model="site_name" label="{{ __('Site Name') }}" required />
                <flux:input wire:model="site_email" label="{{ __('Site Email') }}" type="email" />
                <flux:input wire:model="site_phone" label="{{ __('Phone Number') }}" />
                <flux:input wire:model="site_address" label="{{ __('Address') }}" />
            </div>

            <div class="border-t pt-6">
                <flux:heading size="md" class="mb-4">{{ __('Social Links') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input wire:model="facebook_url" label="Facebook" />
                    <flux:input wire:model="twitter_url" label="Twitter" />
                    <flux:input wire:model="instagram_url" label="Instagram" />
                </div>
            </div>

            <div class="border-t pt-6">
                <flux:heading size="md" class="mb-4">{{ __('Branding') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model="logo" type="file" label="{{ __('Site Logo') }}" accept="image/*" />
                    <flux:input wire:model="site_favicon" type="file" label="{{ __('Favicon') }}" accept="image/*" />
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
