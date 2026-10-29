@blaze(fold: true, memo: true)

{{-- Credit: Heroicons (https://heroicons.com) --}}

@props([
    'icon' => null,
    'name' => null,
])

@php
    $icon = $name ?? $icon;
    $variant = $attributes->get('variant', 'outline');

    if (in_array($variant, ['light', 'duotone'], true) && (! is_string($icon) || preg_match('/-ph(?:-|$)/', $icon) !== 1)) {
        throw new \InvalidArgumentException("The [{$variant}] variant is only supported by Phosphor icons.");
    }
@endphp

<flux:delegate-component :component="'icon.'.$icon">{{ $slot }}</flux:delegate-component>
