<?php

use Illuminate\Support\Facades\Blade;

test('active Flux buttons render a persistent style and pressed state', function (string $variant, string $activeClass) {
    $rendered = Blade::render('<flux:button variant="'.$variant.'" active>Option</flux:button>');

    expect($rendered)
        ->toContain('aria-pressed="true"')
        ->toContain($activeClass);
})->with([
    'primary buttons' => ['primary', 'color-mix(in_oklab,_var(--color-accent),_black_14%)'],
    'danger buttons' => ['danger', 'bg-red-700'],
    'filled buttons' => ['filled', 'bg-zinc-800/10'],
    'filled bordered buttons' => ['filled-bordered', 'border-zinc-300'],
    'outline buttons' => ['outline', 'bg-zinc-100'],
    'ghost buttons' => ['ghost', 'bg-zinc-800/10'],
    'subtle buttons' => ['subtle', 'bg-zinc-800/10'],
]);

test('active colored Flux buttons retain their color family', function (string $variant, string $activeClass) {
    $rendered = Blade::render('<flux:button variant="'.$variant.'" color="blue" active>Option</flux:button>');

    expect($rendered)
        ->toContain('aria-pressed="true"')
        ->toContain($activeClass);
})->with([
    'filled buttons' => ['filled', 'var(--color-button-soft)'],
    'filled bordered buttons' => ['filled-bordered', 'var(--color-button-strong)'],
    'outline buttons' => ['outline', 'var(--color-button-border)'],
    'ghost buttons' => ['ghost', 'var(--color-button-soft)'],
    'subtle buttons' => ['subtle', 'var(--color-button-soft)'],
]);

test('inactive buttons have no pressed state and links do not get button pressed semantics', function () {
    $inactiveButton = Blade::render('<flux:button>Option</flux:button>');
    $activeLink = Blade::render('<flux:button href="/settings" active>Settings</flux:button>');

    expect($inactiveButton)->not->toContain('aria-pressed');
    expect($activeLink)
        ->toContain('href="/settings"')
        ->not->toContain('aria-pressed');
});

test('explicit pressed state takes precedence over the active prop', function () {
    $rendered = Blade::render('<flux:button active aria-pressed="mixed">Option</flux:button>');

    expect($rendered)->toContain('aria-pressed="mixed"');
});
