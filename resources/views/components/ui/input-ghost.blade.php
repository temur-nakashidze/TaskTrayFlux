@props(['value' => '', 'focusOn' => null, 'focusId' => null])

<input type="text" value="{{ $value }}" x-data="{ original: @js($value) }" x-on:keydown.enter.prevent="$el.blur()"
    x-on:keydown.escape="$el.value = original; $el.blur()"
    @if ($focusOn) x-on:{{ $focusOn }}.window="
            if (@js($focusId) === null || String($event.detail?.id) === String(@js($focusId))) {
                $nextTick(() => requestAnimationFrame(() => {
                    $el.focus({ preventScroll: true })
                    $el.select()
                }))
            }
        " @endif
    placeholder="Untitled"
    x-on:blur="
        const v = $el.value.trim()
        if (v === '') { $el.value = original; return }
        if (v !== original) {
            original = v
            $el.value = v
            $dispatch('save', v)
        }
    "
    {{ $attributes->class('field-sizing-content min-w-16 max-w-full bg-transparent border-none outline-none! ring-0 rounded px-1 ') }} />
