@props(['tray', 'badgeColor'])

<flux:card
    {{ $attributes->class('max-w-84 w-full max-h-full flex flex-col shrink-0  p-3 rounded-md! bg-white/30 dark:bg-zinc-800/30') }}
    :highlight="true"
    variant="soft"
    body="inset"
    size="sm"
>
    <x-ui.context-menu>
        <flux:card.header class="group/tray shrink-0">
            <div class="flex items-center gap-2">
                <flux:icon.grip-vertical
                    wire:sort:handle
                    variant="micro"
                    class="-ml-3 cursor-grab text-zinc-600 opacity-20 transition-opacity group-hover/tray:opacity-80 dark:text-zinc-200"
                />
                <div class="flex items-center gap-4">
                    <x-ui.input-ghost
                        wire:key="tray-name-{{ $tray->id }}"
                        focus-on="tray-added"
                        :focus-id="$tray->id"
                        :value="$tray->name"
                        maxlength="255"
                        x-on:save="$wire.renameTray({{ $tray->id }}, $event.detail)"
                        class="font-display text-xl font-medium text-zinc-600 dark:text-zinc-200"
                    />
                    <flux:badge color="{{ $badgeColor }}">{{ $tray->active_tasks_count }}</flux:badge>
                </div>
            </div>
        </flux:card.header>
        <x-slot:menu>
            <x-ui.context-menu.item
                wire:click="$dispatch('open-tray-trash', { trayId: {{ $tray->id }}})"
                icon="archive-box"
            >
                Open Trash
            </x-ui.context-menu.item>
            <x-ui.context-menu.item danger wire:click="deleteTray({{ $tray->id }})" icon="trash">
                Delete
            </x-ui.context-menu.item>
        </x-slot:menu>
    </x-ui.context-menu>
    <div class="mb-4">
        <x-ui.task-router
            :tags="$this->tagOptions"
            x-on:task-submit="$wire.addTask({{ $tray->id }}, $event.detail.title, $event.detail.tags)"
        >
            <x-ui.bump-textarea
                wire:key="task-textarea-tray-{{ $tray->id }}"
                rows="1"
                class="rounded-xl! pr-7! pl-4!"
                resize="none"
            >
                <x-slot:placeholder>
                    <flux:icon name="plus" variant="micro" class="text-zinc-300 dark:text-zinc-50/30" />
                    <flux:text variant="subtle">Add Task</flux:text>
                </x-slot:placeholder>
            </x-ui.bump-textarea>
        </x-ui.task-router>
    </div>
    {{ $slot }}
    @if ($tray->completed_tasks_count > 0)
        <flux:card.footer
            class="overflow-x-hidden overflow-y-auto overscroll-contain px-0"
            x-data="{ showCompleted: false }"
        >
            <flux:button
                x-on:click="showCompleted = ! showCompleted"
                size="xs"
                variant="subtle"
                class="flex items-center justify-start gap-2"
                icon="chevron-down"
            >
                Completed {{ $tray->completed_tasks_count }}
            </flux:button>
            <div
                wire:sort="moveTask"
                wire:sort:config="{ forceFallback: true }"
                x-cloak
                wire:sort:group="tray"
                x-cloak
                x-show="showCompleted"
                class="mt-2 space-y-2"
                x-collapse
            >
                {{ $completed }}
            </div>
        </flux:card.footer>
    @endif
</flux:card>
