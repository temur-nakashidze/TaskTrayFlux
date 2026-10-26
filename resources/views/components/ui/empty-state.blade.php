@props(['icon' => 'inbox', 'heading', 'description'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 py-10 text-center']) }}>
    <flux:icon :name="$icon" class="size-10 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
    <flux:heading size="lg" class="font-semibold!">{{ $heading }}</flux:heading>
    <flux:text variant="muted" class="max-w-sm">{{ $description }}</flux:text>

    @isset($action)
        <div class="pt-1">
            {{ $action }}
        </div>
    @endisset
</div>
