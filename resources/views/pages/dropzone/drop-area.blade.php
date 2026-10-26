<flux:card body="inset" variant="soft" size="sm">
    <flux:card.header>
        <div class="flex items-center gap-4">
            <flux:heading class="font-display font-bold! text-accent tracking-tight" size="2xl">Drop Zone
            </flux:heading>
            <flux:badge size="lg">{{ $this->dropzoneTaskCount }}</flux:badge>
        </div>
        <flux:card.actions>
            <flux:button variant="filled" icon="arrows-up-down"></flux:button>
        </flux:card.actions>
    </flux:card.header>
    <div class="mb-4">
        <x-ui.task-router :routes="$this->routes" :tags="$this->tagOptions"
            x-on:task-submit="$dispatch('add-routed-task', $event.detail)">
            <x-ui.bump-textarea wire:key="task-textarea-dropzone" rows="1" class="pl-4! pr-7! rounded-xl!"
                resize="none">
                <x-slot:placeholder>
                    <flux:icon name="plus" variant="micro" class="text-zinc-400" />
                    <flux:text class="font-medium" variant="subtle">Add Task</flux:text>
                </x-slot:placeholder>
            </x-ui.bump-textarea>
        </x-ui.task-router>
    </div>

    <div class="space-y-2">
        @if ($this->dropzoneTaskCount === 0)
            <flux:card.body size="sm">
                <x-ui.empty-state heading="No tasks in the Dropzone"
                    description="Tasks without a project belong here. Add one above or route a task here." />
            </flux:card.body>
        @endif
    </div>
</flux:card>
