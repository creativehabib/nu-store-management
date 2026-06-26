<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="py-6 w-full">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
