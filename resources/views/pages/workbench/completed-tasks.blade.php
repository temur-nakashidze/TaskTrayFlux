<flux:card variant="filled" body="inset" class="h-full" size="sm">
    <flux:card.header>
        <flux:heading size="xl" variant="muted">Done</flux:heading>
        <flux:card.actions>
            <flux:button icon="broom-sparkles" wire:click="clearCompleted" variant="filled">End day
            </flux:button>
        </flux:card.actions>
    </flux:card.header>
    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->completedTasks as $completedTask)
            <x-project.task :task="$completedTask->task"wire:transition wire:key="uncompleted-task-{{ $completedTask->id }}"
                :tags="$completedTask->task->tags" wire:sort:item="{{ $completedTask->id }}" class="opacity-60">
                <x-slot:actions>
                    <x-task-timer :task="$completedTask->task" />
                    <flux:button variant="filled" icon="arrow-uturn-up"
                        x-on:click="$wire.restoreTask({{ $completedTask->id }})" iconVariant="outline" size="sm">
                    </flux:button>
                </x-slot:actions>
            </x-project.task>
        @endforeach
    </div>
</flux:card>
