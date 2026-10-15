@props(['name' => 'board', 'scrollOn' => null])

<div class="relative pin-board" x-data="{
    fadeL: false,
    fadeR: false,
    updateFades() {
        const el = this.$refs.fadeBoard
        this.fadeL = el.scrollLeft > 1
        this.fadeR = el.scrollLeft + el.clientWidth < el.scrollWidth - 1
    },
}" x-init="const el = $refs.fadeBoard
new ResizeObserver(() => updateFades()).observe(el)
new MutationObserver(() => updateFades()).observe(el, { childList: true })
updateFades()">

    <div x-ref="fadeBoard" x-data="panBoard" x-init="const ro = new ResizeObserver(() => updateFades())
    const watch = () => {
        ro.observe($el);
        [...$el.children].forEach(c => ro.observe(c))
    }
    watch()
    new MutationObserver(() => {
        watch();
        updateFades()
    }).observe($el, { childList: true })
    
    updateFades()
    requestAnimationFrame(() => updateFades())
    setTimeout(() => updateFades(), 300)" x-on:load.window="updateFades()"
        x-on:livewire:navigated.window="$nextTick(() => updateFades())" x-on:scroll.passive="updateFades()"
        x-on:pointerdown="down($event)" x-on:pointermove.window="move($event)" x-on:pointerup.window="up()"
        x-on:pointercancel.window="up()" @if ($scrollOn)
        x-on:{{ $scrollOn }}.window="setTimeout(() => $el.scrollTo({ left: $el.scrollWidth, behavior: 'smooth' }), 300)"
        @endif
        x-bind:class="panning ? 'cursor-grabbing select-none' : 'cursor-default'"
        {{ $attributes->class('pan-board flex items-start gap-4 overflow-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden')->merge(['style' => 'view-transition-name: ' . $name]) }}>
        {{ $slot }}
    </div>

    <div aria-hidden="true"
        class="pointer-events-none absolute inset-y-0 -left-6 z-10 w-16 transition-opacity duration-200"
        style="opacity: 0; view-transition-name: {{ $name }}-fade-left; background: linear-gradient(to right, var(--board-bg, white) 1.5rem, transparent);"
        x-bind:style="{ opacity: fadeL ? 1 : 0 }"></div>

    <div aria-hidden="true"
        class="pointer-events-none absolute inset-y-0 -right-6 z-10 w-16 transition-opacity duration-200"
        style="opacity: 0; view-transition-name: {{ $name }}-fade-right; background: linear-gradient(to left, var(--board-bg, white) 1.5rem, transparent);"
        x-bind:style="{ opacity: fadeR ? 1 : 0 }"></div>
</div>
