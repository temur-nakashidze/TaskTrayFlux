@props(['task', 'hideZero' => false])

@php
    $entriesSeconds = (int) $task->timeEntries
        ->whereNotNull('ended_at')
        ->sum(fn($e) => abs($e->ended_at->diffInSeconds($e->started_at)));

    $elapsed = (int) $task->duration + $entriesSeconds;
    $startedAt = $task->runningTimeEntry?->started_at?->timestamp;
@endphp

@unless ($hideZero && $elapsed === 0 && !$startedAt)
    <div wire:key="timer-{{ $task->id }}-{{ $startedAt ?? 'stopped' }}-{{ $elapsed }}" x-data="taskTimer({{ $elapsed }}, {{ $startedAt ?? 'null' }}, {{ now()->timestamp }})">
        <flux:text x-text="label">{{ gmdate($elapsed >= 3600 ? 'H:i:s' : 'i:s', $elapsed) }}</flux:text>
    </div>
@endunless
