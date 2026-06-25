@props([
    'name',
    'action',
    'title' => __('Are you sure you want to delete this?'),
    'description' => __('Once deleted, this data will be permanently removed. This action cannot be undone.'),
    'buttonText' => __('Delete')
])

<flux:modal :name="$name" class="max-w-lg">
    <form wire:submit="{{ $action }}" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>

            <flux:subheading>
                {{ $description }}
            </flux:subheading>
        </div>

        <!-- ঐচ্ছিক স্লট, যদি কখনো পাসওয়ার্ড বা অন্য কোনো ফিল্ড যুক্ত করার প্রয়োজন হয় -->
        {{ $slot }}

        <div class="flex justify-end gap-2 mt-2">
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" wire:loading.attr="disabled" wire:target="{{ $action }}">
                {{ $buttonText }}
            </flux:button>
        </div>
    </form>
</flux:modal>
