@props(['value' => ''])

<input type="text" value="{{ $value }}" x-data="{ original: @js($value) }" x-on:keydown.enter.prevent="$el.blur()"
    x-on:keydown.escape="$el.value = original; $el.blur()" placeholder="Untitled"
    x-on:blur="
        const v = $el.value.trim()
        if (v === '') { $el.value = original; return }
        if (v !== original) {
            original = v
            $el.value = v
            $dispatch('save', v)
        }
    "
    {{ $attributes->class('block w-full bg-transparent border-none outline-none! ring-0 rounded px-1 -mx-1') }} />
