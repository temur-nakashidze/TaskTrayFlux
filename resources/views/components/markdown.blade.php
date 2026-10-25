@props(['text' => ''])

<div {{ $attributes->class('prose prose-zinc dark:prose-invert max-w-none [overflow-wrap:anywhere]') }}>
    {!! Str::markdown($text ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
</div>
