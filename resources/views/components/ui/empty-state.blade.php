@props(['icon' => 'inbox', 'heading', 'description'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 py-6 text-center']) }}>
    <flux:icon :name="$icon" class="size-10 text-zinc-600/30 dark:text-zinc-500" aria-hidden="true" />
    <flux:heading size="lg" variant="subtle">{{ $heading }}</flux:heading>
    <flux:text variant="subtle" class="max-w-sm">{{ $description }}</flux:text>

    @isset($action)
        <div class="pt-1">{{ $action }}</div>
    @endisset
</div>
