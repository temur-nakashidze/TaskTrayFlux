@props([
    'href' => null,
    'current' => false,
    'open' => null,
    'persist' => null,
    'color' => null,
    'handle' => false,
    'item' => 'null',
    'context' => 'false',
    'workbench' => true,
    'workbenchActive' => false,
    'expandable' => false,
    'compactHref' => null,
    'compactLabel' => null,
    'compactTitle' => null,
    'compactColor' => '',
])

@php
    $initial = $open ?? $current ? 'true' : 'false';
    $hasContent = $expandable;
    $isNavigable = filled($href);

    $openExpr = $persist ? '$persist('.$initial.').as(\''.$persist.'\')' : $initial;

    $xData = '{ open: '.$openExpr.', ready: false, t: null }';

    $onEnter = $hasContent
        ? "if (document.body.dataset.dragging === 'task' && !open) { clearTimeout(t); t = setTimeout(() => open = true, 350) }"
        : '';
    $onLeave = $hasContent ? 'if (!$el.contains($event.relatedTarget)) clearTimeout(t)' : '';
@endphp

<div
    x-data="{{ $xData }}"
    x-init="$nextTick(() => requestAnimationFrame(() => (ready = true)))"
    x-on:dragenter="{{ $onEnter }}"
    x-on:dragleave="{{ $onLeave }}"
    {{
        $attributes->class([
            'group/branch rounded-sm p-1 border hover:bg-white hover:border-zinc-900/20 dark:hover:bg-zinc-800/60 dark:hover:border-zinc-50/10',
            'in-data-flux-sidebar-collapsed-desktop:border-transparent in-data-flux-sidebar-collapsed-desktop:bg-transparent in-data-flux-sidebar-collapsed-desktop:p-0',
            'bg-white/50 dark:bg-zinc-800/20 border-zinc-900/10 dark:border-zinc-50/5' => ! $current,
            'bg-white border-zinc-900/20 dark:bg-zinc-800/60 dark:border-zinc-50/10' => $current,
        ])
    }}
>
    <div
        wire:key="item-{{ $item->id }}"
        wire:sort:item="{{ $item->id }}"
        class="flex items-center gap-1 text-sm in-data-flux-sidebar-collapsed-desktop:justify-center"
    >
        @if ($handle)
            <div class="in-data-flux-sidebar-collapsed-desktop:hidden">
                <x-ui.handle parent="branch" />
            </div>
        @endif

        <div class="min-w-0 flex-1 *:w-full in-data-flux-sidebar-collapsed-desktop:hidden">
            @if ($context)
                <x-ui.context-menu>
            @endif
            <div
                @if ($isNavigable)
                    x-on:click="
                        if ($event.target.closest('a, button')) return;
                        Livewire.navigate('{{ $href }}')
                    "
                @endif
                draggable="false"
                class="font-display font-medium text-zinc-600 dark:text-zinc-50/70 group-hover/branch:text-zinc-700 dark:group-hover/branch:text-zinc-50 tracking-tight flex items-center gap-1 {{ $isNavigable ? 'cursor-pointer' : 'cursor-default' }}"
            >
                {{ $label }}
                <flux:spacer />
                <div class="flex items-center gap-1">
                    @if ($workbench)
                        <flux:button
                            :href="$workbenchActive ? null : route('workbench', $item)"
                            :disabled="$workbenchActive"
                            icon="pencil-ruler"
                            size="xs"
                            variant="filled"
                            color="{{ $color }}"
                        >
                            {{ $item->workbench_tasks_count }}
                        </flux:button>
                    @endisset
                </div>
            </div>

            @if ($context)
                <x-slot:menu>{{ $contextMenu }}</x-slot:menu>
                </x-ui.context-menu>
            @endif
        </div>

        @if (filled($compactLabel))
            @if ($context)
                <x-ui.context-menu>
            @endif
            <div class="hidden in-data-flux-sidebar-collapsed-desktop:flex in-data-flux-sidebar-collapsed-desktop:justify-center">
                <flux:tooltip :content="$compactTitle ?? $compactLabel" position="right">
                    <a
                        href="{{ $compactHref ?? $href }}"
                        wire:navigate
                        wire:sort:handle
                        aria-label="{{ $compactTitle ?? $compactLabel }}"
                        class="inline-flex size-8 items-center justify-center rounded-lg border font-display font-semibold text-sm transition-colors {{ $compactColor }} {{ $current ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-50/20' : '' }}"
                    >
                        {{ $compactLabel }}
                    </a>
                </flux:tooltip>
            </div>
            @if ($context)
                <x-slot:menu>{{ $contextMenu }}</x-slot:menu>
                </x-ui.context-menu>
            @endif
        @endif

        @if ($hasContent)
            <button
                type="button"
                x-on:click.stop="open = ! open"
                x-bind:aria-expanded="open"
                aria-label="Toggle"
                class="-mr-1 shrink-0 cursor-pointer p-1.5 text-zinc-400 hover:text-zinc-600 in-data-flux-sidebar-collapsed-desktop:hidden dark:hover:text-zinc-200"
            >
                <flux:icon.chevron-down
                    class="size-4 {{ $initial === 'true' ? 'rotate-180' : '' }}"
                    x-bind:class="{ 'rotate-180': open, 'transition-transform duration-100': ready }"
                />
            </button>
        @endif
    </div>

    @if ($hasContent)
        <div
            x-show="open"
            x-collapse.duration.100ms
            x-cloak
            class="-mx-1.5 font-mono in-data-flux-sidebar-collapsed-desktop:hidden"
        >
            <flux:separator variant="subtle" class="mt-1" />
            <div class="px-2">{{ $slot }}</div>
        </div>
    @endif
</div>
