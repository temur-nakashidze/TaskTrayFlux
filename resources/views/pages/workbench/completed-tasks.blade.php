<flux:card variant="filled" class="h-full p-1" size="sm">
    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->completedTasks as $completedTask)
            <flux:card size="xs" wire:transition wire:key="task-{{ $completedTask->id }}"
                wire:sort:item="{{ $completedTask->id }}" variant="outline" class="group/task">
                <flux:card.header>
                    <flux:card.heading>
                        <div class="flex items-start gap-1">
                            <flux:icon.grip-vertical wire:sort:handle variant="mini"
                                class="opacity-20 group-hover/task:opacity-80 transition-opacity cursor-grab text-zinc-500" />
                            <flux:text>{{ $completedTask->task->title }}</flux:text>
                        </div>
                    </flux:card.heading>
                    <flux:card.actions>
                        <x-task-timer :task="$completedTask->task" />
                        <flux:button variant="filled" icon="arrow-uturn-up"
                            x-on:click="$wire.restoreTask({{ $completedTask->id }})" iconVariant="outline"
                            size="sm">
                        </flux:button>
                    </flux:card.actions>
                </flux:card.header>
                @if ($completedTask->task->cuts->isNotEmpty())
                    <div x-sort class="space-y-1">
                        @foreach ($uncompletedTask->task->cuts as $cut)
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
            </flux:card>
        @endforeach
    </div>
</flux:card>
