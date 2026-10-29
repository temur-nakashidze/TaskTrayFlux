<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-100 subpixel-antialiased dark:bg-zinc-900">
    <flux:sidebar
        class="sidebar-no-resize w-80 overflow-x-clip bg-zinc-100 transition-[width] duration-300 ease-in-out in-data-flux-sidebar-collapsed-desktop:w-16 motion-reduce:transition-none dark:bg-zinc-950"
        style="view-transition-name: sidebar"
        sticky
        collapsible
    >
        <flux:sidebar.header class="-mx-4 -mt-4 border-b border-zinc-200/50 px-4 py-4 in-data-flux-sidebar-collapsed-desktop:-mx-2 dark:border-zinc-800/60">
            <div class="group/sidebar-logo relative flex min-w-0 flex-1 items-center gap-2 in-data-flux-sidebar-collapsed-desktop:flex-none in-data-flux-sidebar-collapsed-desktop:justify-center">
                <div class="in-data-flux-sidebar-collapsed-desktop:group-hover/sidebar-logo:pointer-events-none in-data-flux-sidebar-collapsed-desktop:group-hover/sidebar-logo:opacity-0">
                    <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                </div>
                <flux:sidebar.collapse class="sidebar-collapse-toggle in-data-flux-sidebar-collapsed-desktop:start-1/2 in-data-flux-sidebar-collapsed-desktop:top-1/2 in-data-flux-sidebar-collapsed-desktop:-translate-x-1/2 in-data-flux-sidebar-collapsed-desktop:-translate-y-1/2 in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </div>
        </flux:sidebar.header>

        <div class="in-data-flux-sidebar-collapsed-desktop:hidden">
            <flux:input as="button" variant="filled" icon="magnifying-glass" kbd="⌘P" />
        </div>
        <livewire:sidebar.projects-list />
        <livewire:sidebar.tags />

        <flux:spacer />

        <div class="in-data-flux-sidebar-collapsed-desktop:hidden">
            <x-ui.theme-picker />
        </div>
        <div class="in-data-flux-sidebar-collapsed-desktop:hidden">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun" />
                <flux:radio value="dark" icon="moon" />
                <flux:radio value="system" icon="computer-desktop" />
            </flux:radio.group>
        </div>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                        data-test="logout-button"
                    >
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @persist('running-timer')
        <livewire:running-timer />
    @endpersist

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
