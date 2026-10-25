<flux:modal name="project-settings" flyout variant="floating">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Project Settings</flux:heading>
            <flux:text class="mt-2">Make changes to your project</flux:text>
        </div>
        <flux:fieldset>
            <div class="space-y-4">
                <flux:switch label="Workbench" description="Enable workbench funtionality for this project" />

                <flux:separator variant="subtle" />

                <flux:switch label="Shelves" description="Enable Shelves fro this project" />

                <flux:separator variant="subtle" />

                <flux:switch label="Blueprint" description="Enable Blueprint For this project" />
            </div>
        </flux:fieldset>

        <div class="flex">
            <flux:spacer />

            <flux:button type="submit" variant="primary">Save changes</flux:button>
        </div>
    </div>
</flux:modal>
