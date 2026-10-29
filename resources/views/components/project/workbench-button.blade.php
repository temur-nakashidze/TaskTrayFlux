@props(['project', 'count' => 0, 'active' => false, 'time' => null])

@php
    $buttonClass = $active ? 'pointer-events-none' : '';
@endphp

<div class="flex items-stretch gap-1">
    <flux:button.group>
        <flux:button
            href="{{ route('workbench', $project->id) }}"
            wire:navigate
            aria-label="Workbench"
            variant="filled-bordered"
            icon="pencil-ruler-ph"
            color="{{ $project->color }}"
            class="{{ $buttonClass }}"
        >
            {{ $count }}
        </flux:button>

        @if ($active)
            <flux:button
                variant="filled-bordered"
                color="{{ $project->color }}"
                wire:click="$dispatch('close-workbench')"
                aria-label="Close workbench"
                icon="x-mark"
            >
            </flux:button>
        @endif
    </flux:button.group>
</div>
