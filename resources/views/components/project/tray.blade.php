@props(['tray'])

<flux:card {{ $attributes->class('max-w-80 w-full max-h-full flex flex-col shrink-0 group/tray') }} :highlight="true"
    variant="soft" body="inset" size="sm">

    <flux:card.header class="shrink-0">
        <div class="flex items-center gap-2">
            <flux:icon.grip-vertical wire:sort:handle variant="mini"
                class="opacity-20 group-hover/tray:opacity-80 transition-opacity cursor-grab text-zinc-500 -ml-3" />
            <x-ui.input-ghost wire:key="tray-name-{{ $tray->id }}" :value="$tray->name" maxlength="255"
                x-on:save="$wire.renameTray({{ $tray->id }}, $event.detail)"
                class="font-medium text-zinc-600 text-xl font-mono" />
        </div>
        <flux:card.actions>
            <flux:badge color="primary">{{ $tray->tasks_count }}</flux:badge>
            <flux:button icon="x-mark" wire:click="deleteTray({{ $tray->id }})" />
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

    <div wire:sort="moveTask" wire:sort:group="tray" wire:sort:group-id="{{ $tray->id }}"
        class="space-y-1 flex-1 min-h-0 overflow-y-auto overscroll-contain px-1 -mx-1">
        @foreach ($tray->tasks as $task)
            <x-project.task :task="$task" wire:transition wire:key="task-{{ $task->id }}"
                wire:sort:item="{{ $task->id }}" />
        @endforeach
    </div>
</flux:card>
