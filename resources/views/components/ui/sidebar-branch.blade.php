@props(['href', 'current' => false, 'open' => null, 'persist' => null, 'badgeColor' => null, 'handle' => false])

@php
    $initial = $open ?? $current ? 'true' : 'false';
    $hasContent = $slot->isNotEmpty();

    $openExpr = $persist ? '$persist(' . $initial . ').as(\'' . $persist . '\')' : $initial;

    $xData = '{ open: ' . $openExpr . ', ready: false, t: null }';

    $onEnter = $hasContent
        ? "if (document.body.dataset.dragging === 'task' && !open) { clearTimeout(t); t = setTimeout(() => open = true, 350) }"
        : '';
    $onLeave = $hasContent ? 'if (!$el.contains($event.relatedTarget)) clearTimeout(t)' : '';
@endphp

<div x-data="{{ $xData }}" x-init="$nextTick(() => requestAnimationFrame(() => ready = true))" x-on:dragenter="{{ $onEnter }}"
    x-on:dragleave="{{ $onLeave }}" {{ $attributes->class('group/branch') }}>

    <div class="flex items-center gap-1">
        @if ($handle)
            <flux:icon.grip-vertical wire:sort:handle variant="micro"
                class="size-4 shrink-0 cursor-grab text-zinc-400 opacity-0 transition-opacity group-hover/branch:opacity-70" />
        @endif

        <div class="flex-1 min-w-0 *:w-full">
            <flux:sidebar.item :href="$href" :current="$current" :badgeColor="$badgeColor" wire:navigate draggable="false"
                x-on:click="{{ $hasContent ? 'open = true' : '' }}">
                @isset($badge)
                    <x-slot:badge>{{ $badge }}</x-slot:badge>
                @endisset

                {{ $label }}
            </flux:sidebar.item>
        </div>

        @if ($hasContent)
            <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open" aria-label="Toggle"
                class="shrink-0 cursor-pointer rounded-md p-1.5 text-zinc-400 hover:bg-zinc-800/5 hover:text-zinc-600 dark:hover:bg-white/10 dark:hover:text-zinc-200">
                <flux:icon.chevron-down class="size-4 {{ $initial === 'true' ? 'rotate-180' : '' }}"
                    x-bind:class="{ 'rotate-180': open, 'transition-transform duration-100': ready }" />
            </button>
        @endif
    </div>

    @if ($hasContent)
        <div x-show="open" x-collapse.duration.100ms x-cloak>
            {{ $slot }}
        </div>
    @endif
</div>
