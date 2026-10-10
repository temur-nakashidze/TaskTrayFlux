@props(['project', 'count' => 0, 'active' => false, 'time' => null])

@php
    $text = $active ? 'text-white!' : 'text-accent-content!';
    $bg = $active ? 'bg-accent' : 'bg-accent/20 dark:hover:bg-zinc-700';
@endphp

<div class="flex items-stretch gap-1">
    <a href="{{ route('workbench', $project->id) }}" wire:navigate aria-label="Workbench">
        <flux:card size="xs" variant="filled" class="{{ $bg }} h-full py-2 transition-colors">
            <div class="flex items-center gap-3">
                <flux:icon.wrench-screwdriver class="size-7 {{ $text }}" />
                <div>
                    <flux:heading class="flex items-center gap-4 font-semibold text-md! font-mono! {{ $text }}">
                        Workbench
                    </flux:heading>
                    @isset($time)
                        <flux:text size="sm" class="{{ $text }}">{{ $time }}</flux:text>
                    @endisset
                </div>
                <flux:badge color="primary" variant="solid">{{ $count }}</flux:badge>
            </div>
        </flux:card>
    </a>

    @if ($active)
        <button type="button" wire:click="$dispatch('close-workbench')" aria-label="Close workbench"
            class="flex cursor-pointer items-center rounded-lg bg-accent px-2 text-white transition-colors hover:bg-accent/80">
            <flux:icon.x-mark class="size-5" />
        </button>
    @endif
</div>
