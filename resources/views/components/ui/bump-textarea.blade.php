@props([
    'disabled' => false,
    'resize' => 'vertical',
    'name' => $attributes->whereStartsWith('wire:model')->first() ?? $attributes->whereStartsWith('x-model')->first(),
    'rows' => null,
    'invalid' => null,
])

@php
    $rows ??= 3;
    $initialHeight = $rows * 1 + 1.0;

    $classes = [
        'inline-block font-sans w-full outline-none! text-base tracking-snug-wide text-zinc-600',
        match ($resize) {
            'none' => 'resize-none',
            'both' => 'resize',
            'horizontal' => 'resize-x',
            'vertical' => 'resize-y',
        },
    ];
@endphp

<div x-data="{
    isEmpty: true,
    bump: false,
    focused: false,
    resize() {
        const el = this.$refs.textarea;
        this.isEmpty = !el.value.trim();

        el.style.height = 'auto'; // natural height = `rows` lines
        if (el.scrollHeight > el.clientHeight) { // only when the text no longer fits
            el.style.height = (el.scrollHeight + el.offsetHeight - el.clientHeight) + 'px';
        }
    }
}" x-init="isEmpty = !$refs.textarea.value.trim();
if (!isEmpty) resize();
Livewire.hook('commit', ({ succeed }) => succeed(() => $nextTick(() => resize())));"
    class="relative bg-zinc-600/5 dark:bg-zinc-900/40 rounded-sm pt-2 pb-1.5">




    <textarea x-ref="textarea" {{ $attributes->class(Arr::toCssClasses($classes)) }} @disabled($disabled)
        @if ($invalid) aria-invalid="true" data-slot="invalid" @endif data-slot="textarea"
        rows="{{ $rows }}" name="{{ $name }}" x-on:input="resize()" x-on:resize.window="resize()"
        x-on:keydown="resize()" x-on:focus="focused = true" x-on:blur="focused = false"
        x-on:keydown.enter="if ($event.shiftKey) { return; } $event.preventDefault(); bump = true; setTimeout(() => bump = false, 250); $el.closest('form')?.requestSubmit();"></textarea>

    @isset($placeholder)
        <div x-show="isEmpty && !focused" x-transition.opacity.duration.100ms
            class="absolute top-2.5 left-2.5 pointer-events-none flex items-center gap-1">
            {{ $placeholder }}
        </div>
    @endisset

    <div x-show="!isEmpty" x-cloak class="absolute right-2 top-2.5 pointer-events-none">
        <flux:icon name="corner-down-left" class="size-5 transition-all"
            x-bind:style="bump
                ?
                'transform: scale(1.16); transition-duration: 20ms; transition-timing-function: ease-in-out;' :
                'transform: scale(1); transition-duration: 120ms; transition-timing-function: ease-in-out;'"
            x-bind:class="bump ? 'text-zinc-500' : 'text-zinc-400'" />
    </div>


</div>
