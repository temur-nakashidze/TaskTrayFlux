<div x-data="panBoard" x-on:pointerdown="down($event)" x-on:pointermove.window="move($event)"
    x-on:pointerup.window="up()" x-on:pointercancel.window="up()" x-on:wheel="wheel($event)"
    x-bind:class="panning ? 'cursor-grabbing select-none' : 'cursor-default'"
    {{ $attributes->class('flex items-start gap-4 overflow-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden') }}>
    {{ $slot }}
</div>
