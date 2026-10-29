<?php

use App\Enums\TagColor;
use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public bool $adding = false;

    public string $newTagName = '';

    public string $newTagColor = 'default';

    public ?int $selectedTagId = null;

    public string $tagName = '';

    public string $tagColor = 'default';

    public ?int $deletingTagId = null;

    #[Computed]
    public function tags()
    {
        return auth()->user()->tags()->orderBy('name')->get();
    }

    #[Computed]
    public function tagColors(): array
    {
        return [
            'default' => 'bg-zinc-400',
            'red' => 'bg-red-500',
            'orange' => 'bg-orange-500',
            'amber' => 'bg-amber-500',
            'yellow' => 'bg-yellow-500',
            'lime' => 'bg-lime-500',
            'green' => 'bg-green-500',
            'emerald' => 'bg-emerald-500',
            'teal' => 'bg-teal-500',
            'cyan' => 'bg-cyan-500',
            'sky' => 'bg-sky-500',
            'blue' => 'bg-blue-500',
            'indigo' => 'bg-indigo-500',
            'violet' => 'bg-violet-500',
            'purple' => 'bg-purple-500',
            'fuchsia' => 'bg-fuchsia-500',
            'pink' => 'bg-pink-500',
            'rose' => 'bg-rose-500',
        ];
    }

    public function startAdding(): void
    {
        $this->adding = true;
        $this->newTagName = '';
        $this->newTagColor = TagColor::Default->value;
        $this->resetValidation();
    }

    public function cancelAdding(): void
    {
        $this->adding = false;
        $this->newTagName = '';
        $this->newTagColor = TagColor::Default->value;
        $this->resetValidation();
    }

    public function createTag(): void
    {
        $this->newTagName = trim($this->newTagName);

        $validated = $this->validate([
            'newTagName' => ['required', 'string', 'max:50', Rule::unique('tags', 'name')->where('user_id', auth()->id())],
            'newTagColor' => ['required', Rule::enum(TagColor::class)],
        ]);

        auth()
            ->user()
            ->tags()
            ->create([
                'name' => $validated['newTagName'],
                'color' => TagColor::from($validated['newTagColor']),
            ]);

        $this->cancelAdding();
        unset($this->tags);
        $this->dispatch('tags-updated');
    }

    public function editTag(int $tagId): void
    {
        $tag = auth()->user()->tags()->findOrFail($tagId);

        $this->selectedTagId = $tag->id;
        $this->tagName = $tag->name;
        $this->tagColor = ($tag->color ?? TagColor::Default)->value;
        $this->resetValidation();
        $this->modal('edit-tag')->show();
    }

    public function updateTag(): void
    {
        $tag = $this->selectedTag();
        $this->tagName = trim($this->tagName);

        $validated = $this->validate([
            'tagName' => ['required', 'string', 'max:50', Rule::unique('tags', 'name')->where('user_id', auth()->id())->ignore($tag->id)],
            'tagColor' => ['required', Rule::enum(TagColor::class)],
        ]);

        $tag->update([
            'name' => $validated['tagName'],
            'color' => TagColor::from($validated['tagColor']),
        ]);

        $this->reset('selectedTagId', 'tagName', 'tagColor');
        $this->modal('edit-tag')->close();
        unset($this->tags);
        $this->dispatch('tags-updated');
    }

    public function confirmDelete(int $tagId): void
    {
        auth()->user()->tags()->findOrFail($tagId);
        $this->deletingTagId = $tagId;
        $this->modal('delete-tag')->show();
    }

    public function deleteTag(): void
    {
        abort_if($this->deletingTagId === null, 404);

        auth()->user()->tags()->findOrFail($this->deletingTagId)->delete();

        $this->reset('deletingTagId');
        $this->modal('delete-tag')->close();
        unset($this->tags);
        $this->dispatch('tags-updated');
    }

    #[On('tags-updated')]
    public function refreshTags(): void
    {
        unset($this->tags);
    }

    private function selectedTag(): Tag
    {
        abort_if($this->selectedTagId === null, 404);

        return auth()->user()->tags()->findOrFail($this->selectedTagId);
    }
};
?>

<div x-data="{ expanded: true }">
    <div class="group/tag-group mt-4 grid in-data-flux-sidebar-collapsed-desktop:hidden">
        <div class="flex items-center gap-1 px-2">
            <flux:heading size="sm" class="flex-1 font-medium text-zinc-500 dark:text-zinc-400">Tags</flux:heading>
            <flux:button
                x-on:click="
                    $wire.startAdding();
                    expanded = true;
                "
                icon="plus"
                variant="ghost"
                size="xs"
                aria-label="Add tag"
                title="Add tag"
                class="opacity-0 group-hover/tag-group:opacity-100 focus:opacity-100"
            />
            <flux:button
                x-on:click="expanded = ! expanded"
                icon="chevron-down"
                variant="ghost"
                size="xs"
                x-bind:aria-label="expanded ? 'Collapse tags' : 'Expand tags'"
                x-bind:aria-expanded="expanded"
                x-bind:title="expanded ? 'Collapse tags' : 'Expand tags'"
                class="transition-transform"
                x-bind:class="{ '-rotate-180': ! expanded }"
            />
        </div>

        <div x-show="expanded" x-collapse.duration.100ms x-cloak>
            @if ($adding)
                <form wire:submit="createTag" class="flex items-start gap-1 px-2 py-1">
                    <div class="min-w-0 flex-1">
                        <flux:input
                            wire:model="newTagName"
                            placeholder="Tag name"
                            maxlength="50"
                            size="sm"
                            aria-label="New tag name"
                        />
                        <flux:error name="newTagName" />
                        <flux:error name="newTagColor" />
                    </div>
                    <x-ui.color-picker wire:model.live="newTagColor" :colors="$this->tagColors" />
                    <flux:button type="submit" icon="check" variant="ghost" size="xs" aria-label="Save tag" />
                    <flux:button
                        type="button"
                        icon="x-mark"
                        variant="ghost"
                        size="xs"
                        aria-label="Cancel"
                        wire:click="cancelAdding"
                    />
                </form>
            @endif

            <div class="mt-1 space-y-1 px-2">
                @forelse ($this->tags as $tag)
                    <div wire:key="sidebar-tag-{{ $tag->id }}">
                        <x-ui.context-menu class="w-full">
                            <button
                                type="button"
                                class="flex w-full items-center gap-2 rounded-sm px-2 py-1 text-left text-sm text-zinc-600 hover:bg-white/70 dark:text-zinc-300 dark:hover:bg-zinc-800"
                            >
                                <flux:icon.tag class="size-4 shrink-0 {{ $tag->color?->tint() ?? 'text-zinc-400' }}">
                                </flux:icon.tag>
                                <span class="truncate">{{ $tag->name }}</span>
                            </button>
                            <x-slot:menu>
                                <x-ui.context-menu.item icon="pencil" wire:click="editTag({{ $tag->id }})">
                                    Rename or recolor
                                </x-ui.context-menu.item>
                                <x-ui.context-menu.item icon="trash" danger wire:click="confirmDelete({{ $tag->id }})">
                                    Delete
                                </x-ui.context-menu.item>
                            </x-slot:menu>
                        </x-ui.context-menu>
                    </div>
                @empty
                    <div class="px-2 py-2 text-sm text-zinc-500 dark:text-zinc-400">
                        No tags yet.
                        <button type="button" wire:click="startAdding" class="text-accent font-medium hover:underline">
                            Add one
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="hidden in-data-flux-sidebar-collapsed-desktop:block">
        <flux:dropdown hover position="right" align="start" class="flex justify-center">
            <flux:button icon="tag" variant="ghost" size="sm" square aria-label="Tags" title="Tags" />

            <flux:menu keep-open class="min-w-60">
                <flux:menu.group heading="Tags">
                    @if ($adding)
                        <form wire:submit="createTag" class="space-y-2 p-2">
                            <div>
                                <flux:input
                                    wire:model="newTagName"
                                    placeholder="Tag name"
                                    maxlength="50"
                                    size="sm"
                                    aria-label="New tag name"
                                />
                                <flux:error name="newTagName" />
                                <flux:error name="newTagColor" />
                            </div>
                            <x-ui.color-picker wire:model.live="newTagColor" :colors="$this->tagColors" />
                            <div class="flex justify-end gap-1">
                                <flux:button
                                    type="button"
                                    icon="x-mark"
                                    variant="ghost"
                                    size="xs"
                                    aria-label="Cancel"
                                    wire:click="cancelAdding"
                                />
                                <flux:button
                                    type="submit"
                                    icon="check"
                                    variant="ghost"
                                    size="xs"
                                    aria-label="Save tag"
                                />
                            </div>
                        </form>
                    @else
                        <flux:menu.item icon="plus" wire:click="startAdding">Add tag</flux:menu.item>

                        @forelse ($this->tags as $tag)
                            <div wire:key="compact-sidebar-tag-{{ $tag->id }}">
                                <x-ui.context-menu class="w-full">
                                    <flux:menu.item as="button" type="button" keep-open class="w-full">
                                        <span class="flex items-center gap-2">
                                            <flux:icon.tag class="size-4 shrink-0 {{ $tag->color?->tint() ?? 'text-zinc-400' }}" />
                                            <span class="truncate">{{ $tag->name }}</span>
                                        </span>
                                    </flux:menu.item>
                                    <x-slot:menu>
                                        <x-ui.context-menu.item icon="pencil" wire:click="editTag({{ $tag->id }})">
                                            Rename or recolor
                                        </x-ui.context-menu.item>
                                        <x-ui.context-menu.item
                                            icon="trash"
                                            danger
                                            wire:click="confirmDelete({{ $tag->id }})"
                                        >
                                            Delete
                                        </x-ui.context-menu.item>
                                    </x-slot:menu>
                                </x-ui.context-menu>
                            </div>
                        @empty
                            <div class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">No tags yet.</div>
                        @endforelse
                    @endif
                </flux:menu.group>
            </flux:menu>
        </flux:dropdown>
    </div>

    <flux:modal name="edit-tag" class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Edit Tag</flux:heading>

            <flux:input label="Tag name" wire:model="tagName" maxlength="50" />
            <flux:error name="tagName" />
            <flux:field>
                <flux:label>Color</flux:label>
                <x-ui.color-picker wire:model.live="tagColor" :colors="$this->tagColors" />
            </flux:field>
            <flux:error name="tagColor" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="updateTag" variant="primary">Save</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-tag" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete tag?</flux:heading>
                <flux:text class="mt-2"> This removes the tag from all tasks and cannot be undone. </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteTag" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
