<flux:card :highlight="true" variant="soft" body="inset" size="sm" class="min-w-80 w-full h-auto">
    <flux:card.header>
        <div class="flex items-center gap-2">
            <flux:card.heading size="xl" variant="muted" class="font-mono">
                Work To Day
            </flux:card.heading>
        </div>
        <flux:card.actions>
            <flux:badge rounded>12
            </flux:badge>
        </flux:card.actions>
    </flux:card.header>
    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->unplayedTasks as $unplayedTask)
            <flux:card.body wire:transition wire:key="task-{{ $unplayedTask->id }}"
                wire:sort:item="{{ $unplayedTask->id }}" class="group/task">
                <flux:card.header>
                    <flux:card.heading>
                        <div class="flex items-start gap-1">
                            <flux:icon.grip-vertical wire:sort:handle variant="mini"
                                class="opacity-20 group-hover/task:opacity-80 transition-opacity cursor-grab text-zinc-500" />
                            <flux:text variant="strong">{{ $unplayedTask->task->title }}</flux:text>
                        </div>
                    </flux:card.heading>
                    <flux:card.actions>
                        <flux:button.group>
                            <flux:button variant="filled" icon="play"
                                x-on:click="$wire.toggleTimer({{ $unplayedTask->id }})" iconVariant="outline"
                                size="sm">
                            </flux:button>
                            <flux:button x-on:click="$wire.removeTask({{ $unplayedTask->task->id }})" variant="filled"
                                icon="x-mark" iconVariant="outline" size="sm">
                            </flux:button>
                        </flux:button.group>
                    </flux:card.actions>
                </flux:card.header>
                @if ($unplayedTask->task->cuts->isNotEmpty())
                    <div x-sort class="space-y-1">
                        @foreach ($unplayedTask->task->cuts as $cut)
                            <flux:card x-sort:item="{{ $cut->id }}" size="xs" variant="soft"
                                class="p-2 rounded-sm group/cut">
                                <div class="flex items-start gap-1">
                                    <flux:icon.grip-vertical wire:sort:handle variant="mini"
                                        class="opacity-20 group-hover/cut:opacity-80 transition-opacity cursor-grab text-zinc-500" />
                                    <flux:text variant="strong">{{ $cut->title }}</flux:text>
                                </div>
                            </flux:card>
                        @endforeach
                    </div>
                @endif
            </flux:card.body>
        @endforeach
    </div>
</flux:card>
