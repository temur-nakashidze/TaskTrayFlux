<flux:card variant="outline" body="inset" size="sm" class="h-full bg-accent/20 border-accent-content/40">
    <flux:card.header>
        <div class="flex items-center gap-2">
            <flux:card.heading size="xl" variant="muted" class="text-accent-content font-mono">
                Started
            </flux:card.heading>
        </div>
        <flux:card.actions>
            <flux:toggle wire:key="global-toggle-{{ $this->runningTask ? 'on' : 'off' }}" on:icon="pause"
                x-on:change="$wire.toggleGlobalTimer" off:icon="play" off:label="Play last" on:label="Pause all"
                :checked="$this->runningTask !== null" class="border-accent-content/50!"></flux:toggle>
        </flux:card.actions>
    </flux:card.header>
    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->playedTasks as $playedTask)
            <flux:card.body wire:transition wire:key="task-{{ $playedTask->id }}" wire:sort:item="{{ $playedTask->id }}"
                class="group/task border-accent-content/20">
                <flux:card.header>
                    <flux:card.heading>
                        <div class="flex items-start gap-1">
                            <flux:icon.grip-vertical wire:sort:handle variant="mini"
                                class="opacity-20 group-hover/task:opacity-80 transition-opacity cursor-grab text-accent-content" />
                            <flux:text class="text-accent-content font-medium">
                                {{ $playedTask->task->title }}
                            </flux:text>
                        </div>
                    </flux:card.heading>
                    <flux:card.actions>
                        <x-task-timer :task="$playedTask->task" />
                        <flux:toggle
                            wire:key="toggle-{{ $playedTask->id }}-{{ $playedTask->task->runningTimeEntry ? 'on' : 'off' }}"
                            variant="filled" off:icon="play" on:icon="pause"
                            x-on:change="$wire.toggleTimer({{ $playedTask->id }})" iconVariant="outline" size="sm"
                            :checked="$playedTask->task->runningTimeEntry !== null">
                        </flux:toggle>
                        <flux:button.group>
                            <flux:button variant="filled" icon="check"
                                x-on:click="$wire.completeTask({{ $playedTask->id }})" iconVariant="outline"
                                size="sm">
                            </flux:button>
                            <flux:button variant="filled" x-on:click="$wire.removeFromActive({{ $playedTask->id }})"
                                icon="arrow-uturn-right" iconVariant="outline" size="sm">
                            </flux:button>
                        </flux:button.group>
                    </flux:card.actions>
                </flux:card.header>
                @if ($playedTask->task->cuts->isNotEmpty())
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
