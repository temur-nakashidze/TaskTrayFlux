<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main inset class="bg-zinc-50 dark:bg-zinc-800/80 lg:p-6! ml-0!  rounded-md!">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
