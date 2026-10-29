@props(['project', 'active' => false, 'time' => null])

@php
    $buttonClass = $active ? 'pointer-events-none' : '';
@endphp

<div class="flex items-stretch gap-1">
    <flux:button.group>
        <flux:button
            href="{{ route('blueprint', $project->id) }}"
            wire:navigate
            aria-label="Blueprint"
            variant="filled-bordered"
            icon="compass-tool-ph-duotone"
            color="{{ $project->color }}"
            class="{{ $buttonClass }}"
        >
        </flux:button>

        @if ($active)
            <flux:button
                variant="filled-bordered"
                color="{{ $project->color }}"
                wire:navigate
                href="{{ route('project', $project->id) }}"
                aria-label="Close blueprint"
                icon="x-mark"
            >
            </flux:button>
        @endif
    </flux:button.group>
</div>
