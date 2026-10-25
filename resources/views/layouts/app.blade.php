<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="bg-zinc-50/60 dark:bg-zinc-900 lg:p-6! ml-0! border-l border-zinc-200/60 dark:border-zinc-800/60">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
