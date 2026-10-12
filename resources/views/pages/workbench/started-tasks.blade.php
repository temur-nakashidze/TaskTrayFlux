@php
    $color = $project->color ?? 'cyan';
    $isNeutral = in_array($color, ['slate', 'gray', 'zinc', 'neutral', 'stone']);

    if ($isNeutral) {
        // Richer neutral contrast for grays/stone/slate so they pop nicely
        $bgClass = "bg-{$color}-100/60 dark:bg-{$color}-900/30";
        $borderClass = "border-{$color}-300 dark:border-{$color}-700";
        $textClass = "text-{$color}-900 dark:text-{$color}-100";
        $subBorderClass = "border-{$color}-200 dark:border-{$color}-800";
        $toggleBorderClass = "border-{$color}-400 dark:border-{$color}-600!";
    } else {
        // Vibrant colors use your original tint styling
        $bgClass = "bg-{$color}-500/20";
        $borderClass = "border-{$color}-700/40 dark:border-{$color}-600/40";
        $textClass = "text-{$color}-700 dark:text-{$color}-300";
        $subBorderClass = "border-{$color}-700/20 dark:border-{$color}-300/20";
        $toggleBorderClass = "border-{$color}-700/50 dark:border-{$color}-300/50!";
    }
@endphp

<flux:card variant="outline" body="inset" size="sm" class="h-full {{ $bgClass }} {{ $borderClass }}">
    <flux:card.header>
        <div class="flex items-center gap-2">
            <flux:card.heading size="xl" variant="muted" class="{{ $textClass }} font-mono">
                Started
            </flux:card.heading>
        </div>
        <flux:card.actions>
            <flux:toggle wire:key="global-toggle-{{ $this->runningTask ? 'on' : 'off' }}" on:icon="pause"
                x-on:change="$wire.toggleGlobalTimer" off:icon="play" off:label="Play last" on:label="Pause all"
                :checked="$this->runningTask !== null" class="{{ $toggleBorderClass }}"></flux:toggle>
        </flux:card.actions>
    </flux:card.header>

    <div wire:sort="moveTask" class="space-y-1">
        @foreach ($this->playedTasks as $playedTask)
            <x-project.task :task="$playedTask->task"wire:transition wire:key="unplayed-task-{{ $playedTask->id }}"
                :tags="$playedTask->task->tags" wire:sort:item="{{ $playedTask->id }}">
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
