<div x-data="{
    open: false,
    x: 0,
    y: 0,
    show(e) {
        this.x = e.clientX
        this.y = e.clientY
        this.open = true
        this.$nextTick(() => {
            const m = this.$refs.menu
            this.x = Math.max(8, Math.min(this.x, window.innerWidth - m.offsetWidth - 8))
            this.y = Math.max(8, Math.min(this.y, window.innerHeight - m.offsetHeight - 8))
        })
    },
}" x-on:contextmenu.prevent="show($event)"
    x-on:mousedown.window="if (open && !$refs.menu.contains($event.target)) open = false"
    x-on:keydown.escape.window="open = false" x-on:scroll.window.passive="open = false" x-on:resize.window="open = false"
    {{ $attributes }}>
    {{ $slot }}

    <template x-teleport="body">
        <div x-ref="menu" x-show="open" x-cloak x-transition.opacity.duration.100ms x-on:click="open = false"
            x-on:contextmenu.prevent x-bind:style="`left: ${x}px; top: ${y}px`"
            class="fixed z-50 min-w-44 rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
            {{ $menu }}
        </div>
    </template>
</div>
