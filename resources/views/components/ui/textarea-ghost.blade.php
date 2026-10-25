@props(['value' => ''])

<textarea spellcheck="false" rows="1" x-data="{
    original: @js($value),
    resize() {
        if (CSS.supports('field-sizing', 'content')) return
        $el.style.height = 'auto'
        $el.style.height = $el.scrollHeight + 'px'
    },
}" x-init="resize()" x-on:input="resize()"
    x-on:resize.window="resize()" x-on:keydown.enter="if ($event.shiftKey) return; $event.preventDefault(); $el.blur()"
    x-on:keydown.escape="$el.value = original; resize(); $el.blur()"
    x-on:blur="
        const v = $el.value.trim()
        if (v === '') { $el.value = original; resize(); return }
        if (v !== original) {
            original = v
            $el.value = v
            resize()
            $dispatch('save', v)
        }
    "
    {{ $attributes->class('block w-full field-sizing-content resize-none overflow-hidden bg-transparent border-none outline-none! ring-0 rounded px-1 -mx-1 text-zinc-600 dark:text-zinc-200 font-normal') }}>{{ $value }}</textarea>
