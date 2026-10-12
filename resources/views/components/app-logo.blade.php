@props([
    'sidebar' => false,
])

@if ($sidebar)
    <div class="flex items-center gap-2">
        <div name="logo" class="flex aspect-square size-10 items-center justify-center rounded-md bg-accent/10">
            <x-app-logo-icon class="size-8 fill-accent text-white dark:text-black" />
        </div>
        <flux:heading size="lg">{{ config('app.name', 'Laravel') }}</flux:heading>
    </div>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo"
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
