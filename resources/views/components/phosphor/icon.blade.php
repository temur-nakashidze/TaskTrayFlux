@props(['name', 'weight' => null, 'variant' => 'outline'])

@php
    $weight ??= match ($variant) {
        'solid' => 'fill',
        'light' => 'light',
        'duotone' => 'duotone',
        default => 'regular',
    };

    $component = 'phosphor-'.$name.($weight === 'regular' ? '' : "-{$weight}");

    $classes = Flux::classes()
        ->add('shrink-0')
        ->add(
            match ($variant) {
                'mini' => '[:where(&)]:size-5.5',
                'micro' => '[:where(&)]:size-4.5',
                default => '[:where(&)]:size-6',
            },
        );
@endphp

<x-dynamic-component :component="$component" {{ $attributes->class($classes) }} />
