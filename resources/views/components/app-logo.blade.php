@props([
    'sidebar' => false,
])

@if ($sidebar)
    <div class="flex items-center gap-2 in-data-flux-sidebar-collapsed-desktop:justify-center">
        <div
            name="logo"
            class="flex aspect-square size-10 items-center justify-center rounded-md text-zinc-700 in-data-flux-sidebar-collapsed-desktop:size-6"
        >
            <x-app-logo-icon class="size-10 fill-zinc-600 text-white in-data-flux-sidebar-collapsed-desktop:size-6 dark:text-black" />
        </div>
        <flux:heading
            size="xl"
            class="font-display font-semibold! text-zinc-600 in-data-flux-sidebar-collapsed-desktop:hidden"
        >
            {{ config('app.name', 'Laravel') }}
        </flux:heading>
    </div>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot
            name="logo"
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-zinc-600 text-zinc-700"
        >
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
