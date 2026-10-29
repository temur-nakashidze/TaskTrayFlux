@props(['icon' => null, 'danger' => false])

<button
    type="button"
    {{
        $attributes->class([
            'group/menu-item flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm font-medium select-none focus:outline-hidden',
            'text-zinc-700 hover:bg-zinc-50 dark:text-white dark:hover:bg-zinc-600' => ! $danger,
            'text-zinc-700 hover:bg-red-50 hover:text-red-600 dark:text-white dark:hover:bg-red-400/20 dark:hover:text-red-400' => $danger,
        ])
    }}
>
    @if ($icon)
        <flux:icon
            :name="$icon"
            variant="mini"
            class="me-2 opacity-90 transition-opacity group-hover/menu-item:opacity-100"
        />
    @endif
    {{ $slot }}
</button>
