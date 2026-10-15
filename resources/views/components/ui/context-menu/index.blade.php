<div x-data="{
    open: false,
    show(e) {
        const m = this.$refs.menu
        if (!m.matches(':popover-open')) m.showPopover()
        this.open = true
        m.style.left = Math.max(8, Math.min(e.clientX, window.innerWidth - m.offsetWidth - 8)) + 'px'
        m.style.top = Math.max(8, Math.min(e.clientY, window.innerHeight - m.offsetHeight - 8)) + 'px'
    },
    close() {
        if (!this.open) return
        this.open = false
        this.$refs.menu.hidePopover()
    },
}" x-on:contextmenu.prevent="show($event)"
    x-on:mousedown.window="if (open && !$refs.menu.contains($event.target)) close()" x-on:keydown.escape.window="close()"
    x-on:scroll.window.passive="close()" x-on:resize.window="close()" x-on:livewire:navigating.window="close()"
    {{ $attributes->merge(['class' => 'group']) }}>
    {{ $slot }}

    <div x-ref="menu" popover="manual" x-on:click="close()" x-on:contextmenu.prevent.stop
        class="m-0 inset-auto min-w-44 overflow-visible rounded-lg border border-zinc-700/10 bg-white/80 p-1 text-zinc-800 shadow-md shadow-zinc-500/15 backdrop-blur-md dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-white dark:shadow-zinc-950/10">
        {{ $menu }}
    </div>
</div>
