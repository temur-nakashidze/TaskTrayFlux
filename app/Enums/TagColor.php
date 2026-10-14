<?php

namespace App\Enums;

/**
 * Tag colors: the Tailwind hues that <flux:badge> understands, plus Default.
 * Every gray shade collapses into Default (Flux's own neutral look).
 *
 * Class names are written out in full on purpose: Tailwind finds classes by
 * scanning source text, so they must never be built with string interpolation.
 */
enum TagColor: string
{
    case Default = 'default';
    case Red = 'red';
    case Orange = 'orange';
    case Amber = 'amber';
    case Yellow = 'yellow';
    case Lime = 'lime';
    case Green = 'green';
    case Emerald = 'emerald';
    case Teal = 'teal';
    case Cyan = 'cyan';
    case Sky = 'sky';
    case Blue = 'blue';
    case Indigo = 'indigo';
    case Violet = 'violet';
    case Purple = 'purple';
    case Fuchsia = 'fuchsia';
    case Pink = 'pink';
    case Rose = 'rose';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** For selects: ['default' => 'Default', 'red' => 'Red', ...] */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }

    /** A random real color, for tags created on the fly. */
    public static function random(): self
    {
        return collect(self::cases())->reject(fn (self $c) => $c === self::Default)->random();
    }

    /** Value for <flux:badge :color="...">. Null keeps Flux's default look. */
    public function badge(): ?string
    {
        return $this === self::Default ? null : $this->value;
    }

    /** Text color for the row icon, for dropdown rows (icons use currentColor). */
    public function tint(): string
    {
        return match ($this) {
            self::Default => 'text-zinc-400',
            self::Red => 'text-red-500',
            self::Orange => 'text-orange-500',
            self::Amber => 'text-amber-500',
            self::Yellow => 'text-yellow-500',
            self::Lime => 'text-lime-500',
            self::Green => 'text-green-500',
            self::Emerald => 'text-emerald-500',
            self::Teal => 'text-teal-500',
            self::Cyan => 'text-cyan-500',
            self::Sky => 'text-sky-500',
            self::Blue => 'text-blue-500',
            self::Indigo => 'text-indigo-500',
            self::Violet => 'text-violet-500',
            self::Purple => 'text-purple-500',
            self::Fuchsia => 'text-fuchsia-500',
            self::Pink => 'text-pink-500',
            self::Rose => 'text-rose-500',
        };
    }

    /** Chip classes that look like <flux:badge>, for places rendered by Alpine. */
    public function chip(): string
    {
        return match ($this) {
            self::Default => 'bg-zinc-400/20 text-zinc-700 dark:bg-zinc-400/40 dark:text-zinc-200',
            self::Red => 'bg-red-400/20 text-red-700 dark:bg-red-400/40 dark:text-red-200',
            self::Orange => 'bg-orange-400/20 text-orange-700 dark:bg-orange-400/40 dark:text-orange-200',
            self::Amber => 'bg-amber-400/20 text-amber-700 dark:bg-amber-400/40 dark:text-amber-200',
            self::Yellow => 'bg-yellow-400/20 text-yellow-700 dark:bg-yellow-400/40 dark:text-yellow-200',
            self::Lime => 'bg-lime-400/20 text-lime-700 dark:bg-lime-400/40 dark:text-lime-200',
            self::Green => 'bg-green-400/20 text-green-700 dark:bg-green-400/40 dark:text-green-200',
            self::Emerald => 'bg-emerald-400/20 text-emerald-700 dark:bg-emerald-400/40 dark:text-emerald-200',
            self::Teal => 'bg-teal-400/20 text-teal-700 dark:bg-teal-400/40 dark:text-teal-200',
            self::Cyan => 'bg-cyan-400/20 text-cyan-700 dark:bg-cyan-400/40 dark:text-cyan-200',
            self::Sky => 'bg-sky-400/20 text-sky-700 dark:bg-sky-400/40 dark:text-sky-200',
            self::Blue => 'bg-blue-400/20 text-blue-700 dark:bg-blue-400/40 dark:text-blue-200',
            self::Indigo => 'bg-indigo-400/20 text-indigo-700 dark:bg-indigo-400/40 dark:text-indigo-200',
            self::Violet => 'bg-violet-400/20 text-violet-700 dark:bg-violet-400/40 dark:text-violet-200',
            self::Purple => 'bg-purple-400/20 text-purple-700 dark:bg-purple-400/40 dark:text-purple-200',
            self::Fuchsia => 'bg-fuchsia-400/20 text-fuchsia-700 dark:bg-fuchsia-400/40 dark:text-fuchsia-200',
            self::Pink => 'bg-pink-400/20 text-pink-700 dark:bg-pink-400/40 dark:text-pink-200',
            self::Rose => 'bg-rose-400/20 text-rose-700 dark:bg-rose-400/40 dark:text-rose-200',
        };
    }
}
