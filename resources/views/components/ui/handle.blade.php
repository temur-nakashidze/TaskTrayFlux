@props(['size' => 'md', 'parent' => 'parent'])
@php
    $sizeClass = match ($size) {
        'sm' => 'size-3.5',
        'lg' => 'size-5',
        default => 'size-4',
    };
@endphp
<flux:icon.grip-vertical wire:sort:handle variant="micro"
    class="{{ $sizeClass }} shrink-0 ml-0.5 cursor-grab text-zinc-400 opacity-20 transition-opacity group-hover/{{ $parent }}:opacity-70" />
