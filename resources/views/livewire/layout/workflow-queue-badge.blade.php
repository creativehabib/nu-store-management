<span wire:poll.15s="refreshCount">
    @if($count > 0)
        <span class="rounded-full bg-red-600 px-2 py-0.5 text-xs font-semibold leading-none text-white">
            {{ $count }}
        </span>
    @endif
</span>
