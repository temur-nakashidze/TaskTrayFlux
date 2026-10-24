<flux:card :highlight="true" variant="soft" body="inset" size="sm" class="min-w-80 w-full h-auto">
    <flux:card.header>
        <div class="flex items-center gap-2">
            <flux:card.heading size="xl" variant="muted" class="font-mono">
                Work To Day
            </flux:card.heading>
        </div>
        <flux:card.actions>
        </flux:card.actions>
    </flux:card.header>
    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->unplayedTasks as $unplayedTask)
            <x-project.task :task="$unplayedTask->task" wire:transition wire:key="unplayed-task-{{ $unplayedTask->id }}"
                :tags="$unplayedTask->task->tags" wire:sort:item="{{ $unplayedTask->id }}">
                <x-slot:actions>
                    <flux:button.group>
                        <flux:button variant="filled" icon="play"
                            x-on:click="$wire.toggleTimer({{ $unplayedTask->id }})" iconVariant="outline"
                            size="sm">
                        </flux:button>
                        <flux:button x-on:click="$wire.removeTask({{ $unplayedTask->task->id }})" variant="filled"
                            icon="x-mark" iconVariant="outline" size="sm">
                        </flux:button>
                    </flux:button.group>
                </x-slot:actions>
            </x-project.task>
        @endforeach
    </div>
</flux:card>
