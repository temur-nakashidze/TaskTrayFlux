@php($color = $project->color ?? \App\Enums\ProjectColor::Zinc)

<flux:card variant="outline" body="inset" size="sm" class="h-full {{ $color->bg() }} {{ $color->border() }}">
    <flux:card.header>
        <div class="flex items-center gap-2">
            <flux:card.heading size="xl" variant="muted" class="{{ $color->text() }} font-mono">
                Started
            </flux:card.heading>
        </div>
        <flux:card.actions>
            <flux:button icon="{{ $this->runningTask ? 'play' : 'pause' }}" x-on:click="$wire.toggleGlobalTimer"
                wire:key="global-toggle-{{ $this->runningTask ? 'on' : 'off' }}" variant="filled"
                color="{{ $project->color }}"> {{ $this->runningTask ? 'Play Last' : 'Pause all' }}
            </flux:button>
        </flux:card.actions>
    </flux:card.header>

    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->playedTasks as $playedTask)
            <x-project.task :task="$playedTask->task" wire:transition wire:key="unplayed-task-{{ $playedTask->id }}"
                :tags="$playedTask->task->tags" wire:sort:item="{{ $playedTask->id }}" class="{{ $color->subBorder() }}">
                <x-slot:actions>
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
                </x-slot:actions>
            </x-project.task>
        @endforeach
    </div>
</flux:card>
