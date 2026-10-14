@props(['tray', 'badgeColor'])

<flux:card {{ $attributes->class('max-w-84 w-full max-h-full flex flex-col shrink-0 ') }} :highlight="true"
    variant="soft" body="inset" size="sm">
    <flux:card.header class="shrink-0 group/tray">
        <div class="flex items-center gap-2">
            <flux:icon.grip-vertical wire:sort:handle variant="mini"
                class="opacity-20 group-hover/tray:opacity-80 transition-opacity cursor-grab text-zinc-500 -ml-3" />
            <div class="flex items-center gap-4">
                <x-ui.input-ghost wire:key="tray-name-{{ $tray->id }}" focus-on="tray-added" :focus-id="$tray->id"
                    :value="$tray->name" maxlength="255" x-on:save="$wire.renameTray({{ $tray->id }}, $event.detail)"
                    class="font-bold text-accent text-xl font-display" />
                <flux:badge color="{{ $badgeColor }}">{{ $tray->active_tasks_count }}</flux:badge>
            </div>
        </div>
        <flux:card.actions>
            <flux:dropdown>
                <flux:button icon="ellipsis-horizontal" variant="subtle"></flux:button>
                <flux:menu>

                    <flux:menu.item wire:click="$dispatch('open-tray-trash', { trayId: {{ $tray->id }}})"
                        icon="archive-box">open
                        Trash
                    </flux:menu.item>
                    <flux:menu.item variant="danger" wire:click="confirmDelete('tray', {{ $tray->id }})"
                        icon="trash">Delete
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:card.actions>
    </flux:card.header>

    <form wire:submit="addTask({{ $tray->id }})" class="mb-4 shrink-0">
        <x-ui.bump-textarea wire:key="task-textarea-{{ $tray->id }}" wire:model="taskTitles.{{ $tray->id }}"
            rows="1" class="pl-4! pr-7!" resize="none">
            <x-slot:placeholder>
                <flux:icon name="plus" variant="micro" class="text-zinc-400" />
                <flux:text class="font-medium" variant="subtle">Add Task</flux:text>
            </x-slot:placeholder>
        </x-ui.bump-textarea>
    </form>

    {{ $slot }}
    @if ($tray->completed_tasks_count > 0)
        <flux:card.footer class="px-0 overflow-y-auto overflow-x-hidden overscroll-contain" x-data="{ showCompleted: false }">

            <flux:button x-on:click="showCompleted = !showCompleted" size="xs" variant="subtle"
                class="flex items-center justify-start gap-2" icon="chevron-down">
                Completed {{ $tray->completed_tasks_count }}
            </flux:button>
            <div wire:sort="moveTask" wire:sort:group="tray" x-cloak x-show="showCompleted" class="space-y-2 mt-2"
                x-collapse>
                {{ $completed }}
            </div>

        </flux:card.footer>
    @endif
</flux:card>
