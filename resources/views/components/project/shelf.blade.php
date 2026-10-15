<x-ui.context-menu class="max-w-84 w-full">
    <flux:card
        {{ $attributes->merge(['class' => ' max-h-full flex flex-col shrink-0 hover:shadow-none hover:border-accent/30 group-data-[open]:shadow-none group-data-[open]:border-accent/30  transition-colors']) }}
        :highlight="true" body="semeless" size="sm">
        <a href="{{ route('shelf', [$shelf->project_id, $shelf]) }}" wire:navigate
            class="absolute inset-0 rounded-[inherit] focus-visible:outline-2 focus-visible:outline-accent"
            aria-label="Open {{ $shelf->name }}"></a>
        <flux:card.header class="shrink-0 group/shelf">
            <div class="flex items-center gap-2">
                <flux:icon.grip-vertical wire:sort:handle variant="mini"
                    class="opacity-20 group-hover/shelf:opacity-80 transition-opacity cursor-grab text-zinc-500 -ml-3" />
                <div class="flex items-center gap-4">
                    <x-ui.input-ghost wire:key="shelf-name-{{ $shelf->id }}" focus-on="shelf-added" :focus-id="$shelf->id"
                        :value="$shelf->name" maxlength="255"
                        x-on:save="$wire.renameShelf({{ $shelf->id }}, $event.detail)"
                        class="font-bold text-accent text-xl font-display z-10" />
                </div>
            </div>
        </flux:card.header>
        <flux:card.body class="py-0">
            <flux:field>
                <flux:progress value="75" class="h-2.5" color="{{ $color }}" />
                <flux:text class="text-xs">Completed 3 of 7 tasks...</flux:text>
            </flux:field>
        </flux:card.body>
        <flux:card.footer>
            <div class="flex items-center gap-3 text-zinc-500/50 text-sm">
                <div class="flex items-center gap-1">
                    <flux:icon.inbox class="size-4" />
                    3
                </div>
                <flux:spacer />
                <div class="flex items-center  gap-1">
                    <flux:icon.clock class="size-4" />
                    ~3h 20m
                </div>
            </div>
        </flux:card.footer>
    </flux:card>
    <x-slot:menu>
        <x-ui.context-menu.item variant="danger" wire:click="deleteShelf({{ $shelf->id }})" icon="trash">
            Delete
        </x-ui.context-menu.item>
    </x-slot:menu>
</x-ui.context-menu>
