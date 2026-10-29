<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

#[Signature('flux:phosphor {names* : Phosphor icon names in kebab-case} {--weight=* : Additional weights to generate (thin, light, bold, fill, duotone)} {--force : Overwrite existing stubs}')]
#[Description('Generate Flux icon stubs (suffixed with -ph) that render Phosphor icons')]
class FluxPhosphorIcon extends Command
{
    private const SUFFIX = 'ph';

    private const WEIGHTS = ['regular', 'thin', 'light', 'bold', 'fill', 'duotone'];

    private const DEFAULT_WEIGHTS = ['regular', 'fill', 'light', 'duotone'];

    private const STUB = <<<'BLADE'
    @props(['variant' => 'outline'])

    <x-phosphor.icon name="__NAME__" __WEIGHT_ATTRIBUTE__ :$variant {{ $attributes }} />
    BLADE;

    public function handle(): int
    {
        $weights = array_values(array_unique([
            ...self::DEFAULT_WEIGHTS,
            ...$this->option('weight'),
        ]));

        if ($invalid = array_diff($weights, self::WEIGHTS)) {
            $this->components->error('Unknown weight(s): '.implode(', ', $invalid));

            return self::FAILURE;
        }

        $directory = resource_path('views/flux/icon');
        File::ensureDirectoryExists($directory);

        foreach ($this->argument('names') as $input) {
            $name = Str::of($input)->kebab()->chopEnd('-'.self::SUFFIX)->toString();

            foreach ($weights as $weight) {
                $icon = "{$name}-".self::SUFFIX.($weight === 'regular' ? '' : "-{$weight}");
                $path = "{$directory}/{$icon}.blade.php";

                if (File::exists($path) && ! $this->option('force')) {
                    $this->components->warn("Skipped {$icon} (exists, use --force)");

                    continue;
                }

                $weightAttribute = $weight === 'regular' ? '' : 'weight="'.$weight.'"';

                File::put($path, str_replace(
                    ['__NAME__', '__WEIGHT_ATTRIBUTE__'],
                    [$name, $weightAttribute],
                    self::STUB,
                ).PHP_EOL);

                $this->components->info("Created {$icon}");
            }
        }

        return self::SUCCESS;
    }
}
