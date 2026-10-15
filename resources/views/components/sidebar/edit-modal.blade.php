    <flux:modal name="edit-project" class="md:w-94">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add New Project</flux:heading>
            </div>

            <div class="flex items-center gap-2 w-full">
                <flux:input label="Project Name" wire:model="projectName" placeholder="Project name" class="w-full" />
                <flux:field>
                    <flux:label>Color</flux:label>
                    <x-ui.color-picker wire:model.live="projectColor" />
                </flux:field>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>

                <flux:button type="submit" wire:click="editProject" variant="primary">Save</flux:button>
            </div>
        </div>
    </flux:modal>
