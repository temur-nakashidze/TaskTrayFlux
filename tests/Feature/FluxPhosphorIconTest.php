<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\View\ViewException;

test('Phosphor icon generation includes regular fill light and duotone by default', function () {
    $directory = resource_path('views/flux/icon');
    $icons = [
        'test-phosphor-default-ph',
        'test-phosphor-default-ph-fill',
        'test-phosphor-default-ph-light',
        'test-phosphor-default-ph-duotone',
    ];

    try {
        $this->artisan('flux:phosphor test-phosphor-default')
            ->assertExitCode(0);

        foreach ($icons as $icon) {
            expect(File::exists("{$directory}/{$icon}.blade.php"))->toBeTrue();
        }

        expect(File::get("{$directory}/test-phosphor-default-ph.blade.php"))
            ->not->toContain('weight="regular"');
        expect(File::get("{$directory}/test-phosphor-default-ph-fill.blade.php"))
            ->toContain('weight="fill"');
        expect(File::get("{$directory}/test-phosphor-default-ph-light.blade.php"))
            ->toContain('weight="light"');
        expect(File::get("{$directory}/test-phosphor-default-ph-duotone.blade.php"))
            ->toContain('weight="duotone"');
    } finally {
        foreach ($icons as $icon) {
            File::delete("{$directory}/{$icon}.blade.php");
        }
    }
});

test('Phosphor icon generation adds explicitly requested weights to the defaults', function () {
    $directory = resource_path('views/flux/icon');
    $icons = [
        'test-phosphor-extra-ph',
        'test-phosphor-extra-ph-fill',
        'test-phosphor-extra-ph-light',
        'test-phosphor-extra-ph-duotone',
        'test-phosphor-extra-ph-thin',
        'test-phosphor-extra-ph-bold',
    ];

    try {
        $this->artisan('flux:phosphor test-phosphor-extra --weight=thin --weight=bold')
            ->assertExitCode(0);

        foreach ($icons as $icon) {
            expect(File::exists("{$directory}/{$icon}.blade.php"))->toBeTrue();
        }
    } finally {
        foreach ($icons as $icon) {
            File::delete("{$directory}/{$icon}.blade.php");
        }
    }
});

test('Phosphor icon generation rejects unknown weights without creating stubs', function () {
    $path = resource_path('views/flux/icon/test-phosphor-invalid-ph.blade.php');

    $this->artisan('flux:phosphor test-phosphor-invalid --weight=unknown')
        ->assertExitCode(1);

    expect(File::exists($path))->toBeFalse();
});

test('Flux variants select the matching Phosphor weights', function (string $variant, string $weight) {
    $renderedVariant = Blade::render('<flux:icon name="gear-ph" variant="'.$variant.'" />');
    $renderedWeight = Blade::render('<x-phosphor.icon name="gear" weight="'.$weight.'" />');
    $extractSvgContent = static function (string $rendered): string {
        if (preg_match('/<svg\b[^>]*>(.*?)<\/svg>/s', $rendered, $matches) !== 1) {
            throw new RuntimeException('The rendered Phosphor icon did not contain an SVG.');
        }

        return $matches[1];
    };

    expect($extractSvgContent($renderedVariant))->toBe($extractSvgContent($renderedWeight));
})->with([
    'outline is regular' => ['outline', 'regular'],
    'solid is fill' => ['solid', 'fill'],
    'light is light' => ['light', 'light'],
    'duotone is duotone' => ['duotone', 'duotone'],
]);

test('Flux mini and micro variants retain their size behavior for Phosphor icons', function (string $variant) {
    $renderedVariant = Blade::render('<flux:icon name="gear-ph" variant="'.$variant.'" />');
    $renderedRegular = Blade::render('<x-phosphor.icon name="gear" weight="regular" />');
    $extractSvgContent = static function (string $rendered): string {
        if (preg_match('/<svg\b[^>]*>(.*?)<\/svg>/s', $rendered, $matches) !== 1) {
            throw new RuntimeException('The rendered Phosphor icon did not contain an SVG.');
        }

        return $matches[1];
    };

    expect($renderedVariant)->toContain($variant === 'mini' ? 'size-5.5' : 'size-4');
    expect($extractSvgContent($renderedVariant))->toBe($extractSvgContent($renderedRegular));
})->with(['mini', 'micro']);

test('Flux rejects light and duotone variants for non-Phosphor icons', function (string $variant) {
    expect(fn () => Blade::render('<flux:icon name="check" variant="'.$variant.'" />'))
        ->toThrow(ViewException::class, "The [{$variant}] variant is only supported by Phosphor icons.");
})->with(['light', 'duotone']);
