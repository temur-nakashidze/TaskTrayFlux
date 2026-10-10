@props(['task'])

<flux:card.body {{ $attributes->class('group/task') }} data-task-card variant="outline">
    <flux:card.header>
        <flux:card.heading>
            <div class="flex items-start gap-1">
                <flux:icon.grip-vertical wire:sort:handle variant="mini"
                    class="opacity-20 group-hover/task:opacity-80 transition-opacity cursor-grab text-zinc-500" />
                <x-ui.textarea-ghost :value="$task->title"
                    x-on:save="$wire.renameTask({{ $task->id }}, $event.detail)" />
            </div>
        </flux:card.heading>
        <flux:card.actions>
            <flux:toggle onIcon="wrench-screwdriver" offIcon="forward" size="sm" variant="filled"
                icon="wrench-screwdriver" color="primary" x-on:change="$wire.toggleWorkbenchTask({{ $task->id }})"
                :checked="$task->workbenchReference" />
        </flux:card.actions>
    </flux:card.header>

    @if ($task->cuts->isNotEmpty())
        <div x-sort class="space-y-1">
            @foreach ($task->cuts as $cut)
                <flux:card x-sort:item="{{ $cut->id }}" wire:key="cut-{{ $cut->id }}" size="xs"
                    variant="soft" class="p-2 rounded-sm group/cut">
                    <div class="flex items-start gap-1">
                        <flux:icon.grip-vertical wire:sort:handle variant="mini"
                            class="opacity-20 group-hover/cut:opacity-80 transition-opacity cursor-grab text-zinc-500" />
                        <x-ui.textarea-ghost :value="$cut->title"
                            x-on:save="$wire.renameCut({{ $cut->id }}, $event.detail)" />
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</flux:card.body>
