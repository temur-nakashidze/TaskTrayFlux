@props(['task', 'toggleColor', 'tags', 'actions' => null, 'completed' => false, 'context' => null])
<flux:card.body {{ $attributes->merge(['class' => 'group/task relative py-3!']) }} data-task-card>

    <x-ui.context-menu>
        <x-slot:menu>
            <x-ui.context-menu.item icon="notebook-pen" x-on:click="$wire.redactingNote({{ $task->id }})">Add
                Note</x-ui.context-menu.item>
            <x-ui.context-menu.item icon="list-tree" x-on:click="$wire.addCut({{ $task->id }})">Add
                Cut</x-ui.context-menu.item>
            <x-ui.context-menu.item icon="trash" wire:click="deleteTask({{ $task->id }})"
                danger>Delete</x-ui.context-menu.item>
        </x-slot:menu>

        <div class="flex items-start justify-between">
            <flux:icon.grip-vertical wire:sort:handle variant="micro"
                class="opacity-15 group-hover/task:opacity-60 transition-opacity cursor-grab text-zinc-500 mt-2 -ml-1" />

            <x-ui.textarea-ghost :value="$task->title" x-on:save="$wire.renameTask({{ $task->id }}, $event.detail)"
                class="mt-1.5 ml-0.5 mr-1" />
            @if ($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </x-ui.context-menu>
    @if ($task->cuts->isNotEmpty())
        <div x-sort class="space-y-1 mt-2">
            @foreach ($task->cuts as $cut)
                <flux:card x-sort:item="{{ $cut->id }}" wire:key="cut-{{ $cut->id }}" size="xs"
                    variant="soft" class="p-1.5 rounded-sm group/cut">
                    <div class="flex items-start gap-1">
                        <flux:icon.grip-vertical wire:sort:handle variant="micro"
                            class="opacity-15 group-hover/cut:opacity-80 transition-opacity cursor-grab text-zinc-500 mt-1" />
                        <x-ui.textarea-ghost autofocus :value="$cut->title" class="text-zinc-500! mt-0.5"
                            x-on:save="$wire.renameCut({{ $cut->id }}, $event.detail)" />
                        @if (!$completed)
                            <flux:button icon="check" variant="filled" class="shrink-0" size="xs" />
                        @endif
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
    @if ($tags->count() > 0 or $task->note)

        <div class="flex items-start justify-between gap-2 mt-3">
            @if ($tags->count() > 0)
                <div>
                    @foreach ($tags as $tag)
                        <flux:badge color="{{ $tag->color }}" icon="tag" size="sm">{{ $tag->name }}
                        </flux:badge>
                    @endforeach
                </div>
            @endif
            <flux:spacer />
            @if ($task->note)
                <flux:button variant="subtle" size="xs" icon="notebook-text"
                    x-on:click="$wire.redactingNote({{ $task->id }})" />
            @endif
        </div>
    @endif


</flux:card.body>
