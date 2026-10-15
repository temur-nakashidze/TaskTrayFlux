@props([
    'sidebar' => false,
])

@if ($sidebar)
    <div class="flex items-center gap-3 bg-(--color-zinc-200) pr-3 rounded-md">
        <div name="logo"
            class="flex aspect-square size-10 items-center justify-center rounded-md bg-accent/20 text-zinc-700">
            <x-app-logo-icon class="size-8 fill-accent text-white dark:text-black" />
        </div>
        <flux:heading size="lg" class="font-display text-accent-content">{{ config('app.name', 'Laravel') }}
        </flux:heading>
    </div>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo"
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-zinc-600 text-zinc-700">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
