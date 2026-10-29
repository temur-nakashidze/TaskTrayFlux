<div
    x-data="{
        open: false,
        show(e) {
            const m = this.$refs.menu;
            if (! m.matches(':popover-open')) m.showPopover();
            this.open = true;
            m.style.left = Math.max(8, Math.min(e.clientX, window.innerWidth - m.offsetWidth - 8)) + 'px';
            m.style.top = Math.max(8, Math.min(e.clientY, window.innerHeight - m.offsetHeight - 8)) + 'px';
        },
        close() {
            if (! this.open) return;
            this.open = false;
            this.$refs.menu.hidePopover();
        },
    }"
    x-on:contextmenu.prevent="show($event)"
    x-on:mousedown.window="if (open && ! $refs.menu.contains($event.target)) close();"
    x-on:keydown.escape.window="close()"
    x-on:scroll.window.passive="close()"
    x-on:resize.window="close()"
    x-on:livewire:navigating.window="close()"
    {{ $attributes->merge(['class' => 'group']) }}
>
    {{ $slot }}

    <div
        x-ref="menu"
        popover="manual"
        data-ui-context-menu
        x-on:click="close()"
        x-on:contextmenu.prevent.stop
        class="inset-auto m-0 min-w-48 rounded-lg border border-zinc-200 bg-white p-[.3125rem] text-zinc-800 shadow-xs focus:outline-hidden dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
    >
        {{ $menu }}
    </div>
</div>
