@php
    $zinc = [
        'dim-grey' => 'Dim grey',
        'alabaster-grey' => 'Alabaster grey',
        'dusty-grape' => 'Dusty grape',
    ];
    $accent = [
        'mono' => 'Mono',
        'violet' => 'Violet',
        'emerald' => 'Emerald',
    ];
    $accentSwatch = [
        'mono' => 'dim-grey-600',
        'violet' => 'violet-600',
        'emerald' => 'emerald-600',
    ];
@endphp

<div x-data="{
    zinc: localStorage.getItem('theme.zinc') ?? 'dim-grey',
    accent: localStorage.getItem('theme.accent') ?? 'mono',
    labels: @js(['zinc' => $zinc, 'accent' => $accent]),
    set(key, value) {
        this[key] = value
        localStorage.setItem('theme.' + key, value)
        document.documentElement.dataset[key] = value
    },
}">
    <flux:dropdown align="end">
        <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
            <span class="flex items-center gap-1.5">
                <span class="size-3 rounded-full bg-zinc-500"></span>
                <span class="size-3 rounded-full bg-accent"></span>
                <span x-text="labels.zinc[zinc]"></span>
                <span class="text-zinc-400">/</span>
                <span x-text="labels.accent[accent]"></span>
            </span>
        </flux:button>

        <flux:menu>
            <flux:menu.group heading="Base tint">
                @foreach ($zinc as $key => $label)
                    <flux:menu.item x-on:click="set('zinc', '{{ $key }}')">
                        <span class="flex w-full items-center gap-2">
                            <span class="size-3 rounded-full"
                                style="background: var(--color-{{ $key }}-500)"></span>
                            {{ $label }}
                            <flux:icon.check variant="micro" class="ml-auto" x-show="zinc === '{{ $key }}'" />
                        </span>
                    </flux:menu.item>
                @endforeach
            </flux:menu.group>

            <flux:menu.separator />

            <flux:menu.group heading="Accent">
                @foreach ($accent as $key => $label)
                    <flux:menu.item x-on:click="set('accent', '{{ $key }}')">
                        <span class="flex w-full items-center gap-2">
                            <span class="size-3 rounded-full"
                                style="background: var(--color-{{ $accentSwatch[$key] }})"></span>
                            {{ $label }}
                            <flux:icon.check variant="micro" class="ml-auto"
                                x-show="accent === '{{ $key }}'" />
                        </span>
                    </flux:menu.item>
                @endforeach
            </flux:menu.group>
        </flux:menu>
    </flux:dropdown>
</div>
