    <flux:modal name="add-project" class="md:w-140">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add New Project</flux:heading>
            </div>
            <x-ui.color-picker wire:model.live="projectColor" />

            <flux:input label="Project name" wire:model="projectName" placeholder="Project Name" />
            <flux:radio.group wire:model="selectedTemplate" label="Template" variant="cards" class="max-sm:flex-col">
                @foreach ($this->templates as $template)
                    <flux:radio value="{{ $template['key'] }}" label="{{ $template['label'] }}" :checked="$template['key'] === 'default'" />
                @endforeach
            </flux:radio.group>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>

                <flux:button type="submit" wire:click="createProject" variant="primary">Create</flux:button>
            </div>
        </div>
    </flux:modal>
