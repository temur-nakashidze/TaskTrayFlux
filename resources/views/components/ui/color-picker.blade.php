@props([
    'value' => null,
    'colors' => [
        'slate' => 'bg-slate-500',
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
    ],
])

<div x-data="{
    value: @js($value),
    open: false,
    top: 0,
    left: 0,
    toggle() { this.open ? this.close() : this.show() },
    show() {
        const r = this.$refs.trigger.getBoundingClientRect()
        this.top = r.bottom + 6
        this.left = r.left
        this.open = true
        this.$refs.panel.showPopover()
    },
    close() {
        if (!this.open) return
        this.open = false
        this.$refs.panel.hidePopover()
    },
    pick(name) {
        this.value = name
        this.close()
        this.$root.dispatchEvent(new CustomEvent('color-change', { detail: name, bubbles: true }))
    },
}" x-modelable="value"
    x-on:keydown.escape.window="if (open) { $event.preventDefault(); close() }" x-on:scroll.window.capture="close()"
    x-on:resize.window="close()" {{ $attributes->whereStartsWith('wire:model') }}
    {{ $attributes->whereDoesntStartWith('wire:model')->class('inline-flex') }}>

    <flux:button x-ref="trigger" x-on:click="toggle()" variant="outline" aria-label="Choose color">
        @foreach ($colors as $name => $bg)
            <span x-show="value === '{{ $name }}'" x-cloak
                class="size-4 rounded-full {{ $bg }}"></span>
            <span x-show="value === '{{ $name }}'" x-cloak>{{ $name }}</span>
        @endforeach


        <span x-show="!value" class="size-4 rounded-full border border-dashed border-zinc-400"></span>
        <span x-show="!value">None</span>
    </flux:button>

    <div x-ref="panel" popover="manual" x-on:click.outside="if (!$refs.trigger.contains($event.target)) close()"
        x-bind:style="`top: ${top}px; left: ${left}px`"
        class="m-0 inset-auto overflow-visible rounded-lg border border-zinc-200 bg-white p-2 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
        <div class="grid grid-cols-6 gap-1.5">
            @foreach ($colors as $name => $bg)
                <button type="button" title="{{ ucfirst($name) }}" x-on:click="pick('{{ $name }}')"
                    class="grid size-6 place-items-center rounded-full {{ $bg }} outline-offset-2 transition-transform hover:scale-110 focus-visible:outline-2 focus-visible:outline-zinc-900 dark:focus-visible:outline-white">
                    <flux:icon.check variant="micro" class="size-3.5 text-white"
                        x-show="value === '{{ $name }}'" x-cloak />
                </button>
            @endforeach
        </div>
    </div>
</div>
