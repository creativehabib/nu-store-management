<div class="space-y-6">
    <div class="border-b pb-4 mb-4">
        <flux:heading size="xl">{{ __('System Information') }}</flux:heading>
        <p class="text-zinc-500 text-sm mt-1">{{ __('Current server and application environment details.') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- System Environment --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4 border-b pb-2">{{ __('Application Environment') }}</flux:heading>
            <div class="space-y-3">
                @foreach($systemEnvironment as $key => $value)
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">{{ $key }}</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

        {{-- Server Environment --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4 border-b pb-2">{{ __('Server Environment') }}</flux:heading>
            <div class="space-y-3">
                @foreach($serverEnvironment as $key => $value)
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">{{ $key }}</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

        {{-- PHP Configuration --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4 border-b pb-2">{{ __('PHP Configuration') }}</flux:heading>
            <div class="space-y-3">
                @foreach($phpConfiguration as $key => $value)
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">{{ $key }}</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

        {{-- Database Information --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4 border-b pb-2">{{ __('Database Information') }}</flux:heading>
            <div class="space-y-3">
                @foreach($databaseInformation as $key => $value)
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">{{ $key }}</span>
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>

    </div>
</div>
