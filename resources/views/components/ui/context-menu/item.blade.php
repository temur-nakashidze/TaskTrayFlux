@props(['icon' => null, 'danger' => false])

<button type="button"
    {{ $attributes->class([
        'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm',
        'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-700' => !$danger,
        'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10' => $danger,
    ]) }}>
    @if ($icon)
        <flux:icon :name="$icon" variant="micro" />
    @endif
    {{ $slot }}
</button>
