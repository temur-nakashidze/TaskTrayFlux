<?php

use App\Actions\TimeTracking\PauseTaskTimer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function entry()
    {
        return auth()->user()
            ->timeEntries()
            ->whereNull('ended_at')
            ->with('task')
            ->latest('started_at')
            ->first();
    }

    // Saved total of the running task: stored duration plus its finished entries.
    // The running entry itself is added live by Alpine.
    #[Computed]
    public function elapsed(): int
    {
        $task = $this->entry?->task;

        if (! $task) {
            return 0;
        }

        $finished = $task->timeEntries()
            ->whereNotNull('ended_at')
            ->get()
            ->sum(fn ($e) => abs($e->ended_at->diffInSeconds($e->started_at)));

        return (int) $task->duration + (int) $finished;
    }

    #[On('timer-changed')]
    public function refresh(): void
    {
        unset($this->entry, $this->elapsed);
    }

    public function pause(): void
    {
        app(PauseTaskTimer::class)->handle(auth()->user());

        unset($this->entry, $this->elapsed);
    }
};
?>

<div x-data="{ hidden: /\/workbench\/?$/.test(location.pathname) }"
    x-on:livewire:navigated.window="hidden = /\/workbench\/?$/.test(location.pathname); $wire.$refresh()"
    x-show="!hidden" x-cloak
    class="pointer-events-none fixed inset-x-0 bottom-4 z-40 flex justify-center">

    @if ($this->entry)
        @php($task = $this->entry->task)

        <div wire:key="running-{{ $this->entry->id }}"
            x-data="taskTimer({{ $this->elapsed }}, {{ $this->entry->started_at->timestamp }}, {{ now()->timestamp }}, @js($task->title))"
            x-bind:class="seconds > 14400 && 'border-amber-500!'"
            class="pointer-events-auto flex items-center gap-3 rounded-xl border border-zinc-200 bg-white px-4 py-2 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">

            <span class="relative flex size-2.5">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-accent opacity-60"></span>
                <span class="relative inline-flex size-2.5 rounded-full bg-accent"></span>
            </span>

            <a href="{{ route('workbench', $task->project_id) }}" wire:navigate
                class="max-w-64 truncate text-sm font-medium text-zinc-700 hover:underline dark:text-zinc-200">
                {{ $task->title }}
            </a>

            <flux:text class="font-mono" x-text="label"></flux:text>

            <span x-show="seconds > 14400" x-cloak class="text-xs text-amber-600">still running?</span>

            <flux:button size="sm" variant="filled" icon="pause" wire:click="pause" />
        </div>
    @endif
</div>
