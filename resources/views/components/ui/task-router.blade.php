@props(['routes' => [], 'tags' => [], 'mode' => 'all', 'popupClass' => null, 'showRoute' => true])

@php
    $popupClass ??=
        'absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-1 shadow-lg';
@endphp

<div x-data="taskRouter(@js($routes), @js($tags), @js($mode))" x-on:input="detect($event)" x-on:click="detect($event)"
    x-on:keyup="['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes($event.key) && detect($event)"
    x-on:keydown.capture="key($event)" x-on:click.outside="open = false" {{ $attributes->class('relative') }}>

    {{ $slot }}

    <ul x-show="open" x-cloak role="listbox" class="{{ $popupClass }}">
        <template x-for="(item, i) in items" :key="item.kind + (item.id ?? item.name)">
            <li role="option" :aria-selected="i === active" x-on:mousedown.prevent="pick(item)"
                x-on:mouseenter="active = i" :class="i === active && 'bg-zinc-100 dark:bg-zinc-700'"
                class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm">
                <span class="inline-flex shrink-0" :class="item.tint">
                    <flux:icon.panels-top-left x-show="item.kind === 'project'" variant="micro" class="size-4" />
                    <flux:icon.inbox x-show="item.kind === 'tray'" variant="micro" class="size-4" />
                    <flux:icon.tag x-show="item.kind === 'tag' || item.kind === 'new-tag'" variant="micro"
                        class="size-4" />
                </span>
                <span x-text="item.kind === 'new-tag' ? 'Create “' + item.name + '”' : item.name"
                    class="text-zinc-600"></span>
                <span x-show="item.kind === 'tray'" class="ml-auto text-xs text-zinc-400">tray</span>
            </li>
        </template>
        <li x-show="!items.length" class="px-2 py-1.5 text-sm text-zinc-500">No match</li>
    </ul>

    @if ($showRoute)
        <div x-show="route || chosen.length" x-cloak
            class="mt-1 flex flex-wrap items-center gap-1.5 font-mono text-xs text-zinc-500">
            <span x-show="route" class="inline-flex items-center gap-1.5">
                <span class="size-2 rounded-full" :class="route?.project.dot"></span>
                <span x-text="routeLabel"></span>
            </span>
            <template x-for="(t, i) in chosen" :key="t.name">
                <button type="button" x-on:click="removeTag(i)"
                    class="inline-flex items-center gap-1 rounded-sm px-2 py-1" :class="t.chip">
                    <flux:icon.tag variant="micro" />
                    <span x-text="t.name"></span>
                    <span x-show="t.isNew" class="opacity-60">new</span>
                    <span aria-hidden="true">×</span>
                </button>
            </template>
        </div>
    @endif
</div>
