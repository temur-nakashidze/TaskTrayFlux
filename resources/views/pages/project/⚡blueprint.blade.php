<?php

use App\Models\Blueprint;
use App\Models\Project;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Project $project;

    #[Locked]
    public Blueprint $blueprint;

    public ?string $blueprintText = '';

    public bool $editing = false;

    public function mount(Project $project)
    {
        $this->project = $project;
        if (! $project->blueprint) {
            redirect('dashboard');
        }
        $blueprint = $project->blueprint;
        $this->blueprint = $blueprint;

        $this->blueprintText = $blueprint->text;
    }

    public function save(): void
    {
        $this->blueprint->update(['text' => $this->blueprintText]);
        $this->editing = false;
    }

    public function cancel(): void
    {
        $this->blueprintText = $this->blueprint->text ?? '';
        $this->editing = false;
    }
};
?>

<div>
    <livewire:project.project-header :project="$project" />

    @php
        $tools = [
            ['icon' => 'bold', 'command' => 'toggleBold', 'active' => 'bold', 'label' => 'Bold'],
            ['icon' => 'italic', 'command' => 'toggleItalic', 'active' => 'italic', 'label' => 'Italic'],
            ['icon' => 'strikethrough', 'command' => 'toggleStrike', 'active' => 'strike', 'label' => 'Strikethrough'],
            'sep',
            [
                'icon' => 'h2',
                'command' => 'toggleHeading',
                'active' => 'heading',
                'attrs' => ['level' => 2],
                'label' => 'Heading 2',
            ],
            [
                'icon' => 'h3',
                'command' => 'toggleHeading',
                'active' => 'heading',
                'attrs' => ['level' => 3],
                'label' => 'Heading 3',
            ],
            'sep',
            [
                'icon' => 'list-bullet',
                'command' => 'toggleBulletList',
                'active' => 'bulletList',
                'label' => 'Bullet list',
            ],
            [
                'icon' => 'numbered-list',
                'command' => 'toggleOrderedList',
                'active' => 'orderedList',
                'label' => 'Numbered list',
            ],
            [
                'icon' => 'code-bracket',
                'command' => 'toggleCodeBlock',
                'active' => 'codeBlock',
                'label' => 'Code block',
            ],
        ];
    @endphp

    @if ($editing)
        <div wire:key="blueprint-editor" wire:ignore x-data="markdownEditor('blueprintText', @js($blueprintText))"
            class="rounded-lg border border-zinc-200 bg-white transition-colors focus-within:border-zinc-400 focus-within:ring-2 focus-within:ring-accent/20 dark:border-zinc-700 dark:bg-zinc-900 dark:focus-within:border-zinc-500">
            <div
                class="sticky top-0 z-10 flex flex-wrap gap-1 rounded-t-lg border-b border-zinc-200 bg-white/90 p-1.5 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/90">
                @foreach ($tools as $tool)
                    @if ($tool === 'sep')
                        <flux:separator vertical class="mx-1" />
                    @else
                        <flux:button type="button" size="sm" variant="subtle" :icon="$tool['icon']"
                            aria-label="{{ $tool['label'] }}" x-on:mousedown.prevent="null"
                            x-on:click="run('{{ $tool['command'] }}', @js($tool['attrs'] ?? null))"
                            x-bind:class="isActive('{{ $tool['active'] }}', @js($tool['attrs'] ?? null)) &&
                                'bg-zinc-200 dark:bg-zinc-700'" />
                    @endif
                @endforeach
            </div>

            <div x-ref="editor" class="p-4"></div>
        </div>

        <div class="mt-3 flex gap-2">
            <flux:button wire:click="save" variant="primary" size="sm">Save</flux:button>
            <flux:button wire:click="cancel" size="sm">Cancel</flux:button>
        </div>
    @else
        <x-markdown :text="$blueprintText" />

        <flux:button wire:click="$set('editing', true)" size="sm" icon="pencil-square" class="mt-3">Edit
        </flux:button>
    @endif
</div>
