{{-- resources/views/components/ui/lane-divider.blade.php --}}
@props([
    'opacity' => 15,
    'thickness' => 0.5,
    'dash' => 12,
    'gap' => 5,
    'color' => 'var(--color-neutral-600)',
    'darkColor' => 'var(--color-neutral-300)',
])
@once
    <style>
        .lane-divider {
            display: block;
            height: var(--lane-thickness);
            background-image: repeating-linear-gradient(to right,
                    var(--lane-color) 0 var(--lane-dash),
                    transparent var(--lane-dash) calc(var(--lane-dash) + var(--lane-gap)));
        }

        .dark .lane-divider {
            background-image: repeating-linear-gradient(to right,
                    var(--lane-color-dark, var(--lane-color)) 0 var(--lane-dash),
                    transparent var(--lane-dash) calc(var(--lane-dash) + var(--lane-gap)));
        }
    </style>
@endonce
<div {{ $attributes->class('lane-divider') }}
    style="
        opacity: {{ $opacity }}%;
        --lane-thickness: {{ $thickness }}px;
        --lane-dash: {{ $dash }}px;
        --lane-gap: {{ $gap }}px;
        --lane-color: {{ $color }};
        @if ($darkColor) --lane-color-dark: {{ $darkColor }}; @endif
    "
    role="presentation"></div>
