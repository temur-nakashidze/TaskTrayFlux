<?php

namespace App\Enums;

/**
 * Every Tailwind v4 palette color with its project-card classes.
 *
 * All class names are written out in full on purpose: Tailwind finds classes by
 * scanning source text, so they must never be built with string interpolation.
 */
enum ProjectColor: string
{
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
    case Slate = 'slate';
    case Gray = 'gray';
    case Zinc = 'zinc';
    case Neutral = 'neutral';
    case Stone = 'stone';
    case Mauve = 'mauve';
    case Olive = 'olive';
    case Mist = 'mist';
    case Taupe = 'taupe';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function isNeutral(): bool
    {
        return in_array($this, [self::Slate, self::Gray, self::Zinc, self::Neutral, self::Stone, self::Mauve, self::Olive, self::Mist, self::Taupe], true);
    }

    /** For selects: ['cyan' => 'Cyan', ...] */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }

    public function bg(): string
    {
        return $this->styles()['bg'];
    }

    public function border(): string
    {
        return $this->styles()['border'];
    }

    public function text(): string
    {
        return $this->styles()['text'];
    }

    public function subBorder(): string
    {
        return $this->styles()['subBorder'];
    }

    public function toggleBorder(): string
    {
        return $this->styles()['toggleBorder'];
    }

    /** Text color for the row icon, for dropdown rows (icons use currentColor). */
    public function tint(): string
    {
        return match ($this) {
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
            self::Slate => 'text-slate-400',
            self::Gray => 'text-gray-400',
            self::Zinc => 'text-zinc-400',
            self::Neutral => 'text-neutral-400',
            self::Stone => 'text-stone-400',
            self::Mauve => 'text-mauve-400',
            self::Olive => 'text-olive-400',
            self::Mist => 'text-mist-400',
            self::Taupe => 'text-taupe-400',
        };
    }

    /** @return array{bg: string, border: string, text: string, subBorder: string, toggleBorder: string} */
    private function styles(): array
    {
        return match ($this) {
            self::Red => [
                'bg' => 'bg-red-500/20!',
                'border' => 'border-red-700/40 dark:border-red-600/40',
                'text' => 'text-red-700 dark:text-red-300',
                'subBorder' => 'border-red-700/20 dark:border-red-300/20',
                'toggleBorder' => 'border-red-700/50 dark:border-red-300/50!',
            ],
            self::Orange => [
                'bg' => 'bg-orange-500/20!',
                'border' => 'border-orange-700/40 dark:border-orange-600/40',
                'text' => 'text-orange-700 dark:text-orange-300',
                'subBorder' => 'border-orange-700/20 dark:border-orange-300/20',
                'toggleBorder' => 'border-orange-700/50 dark:border-orange-300/50!',
            ],
            self::Amber => [
                'bg' => 'bg-amber-500/20!',
                'border' => 'border-amber-700/40 dark:border-amber-600/40',
                'text' => 'text-amber-700 dark:text-amber-300',
                'subBorder' => 'border-amber-700/20 dark:border-amber-300/20',
                'toggleBorder' => 'border-amber-700/50 dark:border-amber-300/50!',
            ],
            self::Yellow => [
                'bg' => 'bg-yellow-500/20!',
                'border' => 'border-yellow-700/40 dark:border-yellow-600/40',
                'text' => 'text-yellow-700 dark:text-yellow-300',
                'subBorder' => 'border-yellow-700/20 dark:border-yellow-300/20',
                'toggleBorder' => 'border-yellow-700/50 dark:border-yellow-300/50!',
            ],
            self::Lime => [
                'bg' => 'bg-lime-500/20!',
                'border' => 'border-lime-700/40 dark:border-lime-600/40',
                'text' => 'text-lime-700 dark:text-lime-300',
                'subBorder' => 'border-lime-700/20 dark:border-lime-300/20',
                'toggleBorder' => 'border-lime-700/50 dark:border-lime-300/50!',
            ],
            self::Green => [
                'bg' => 'bg-green-500/20!',
                'border' => 'border-green-700/40 dark:border-green-600/40',
                'text' => 'text-green-700 dark:text-green-300',
                'subBorder' => 'border-green-700/20 dark:border-green-300/20',
                'toggleBorder' => 'border-green-700/50 dark:border-green-300/50!',
            ],
            self::Emerald => [
                'bg' => 'bg-emerald-500/20!',
                'border' => 'border-emerald-700/40 dark:border-emerald-600/40',
                'text' => 'text-emerald-700 dark:text-emerald-300',
                'subBorder' => 'border-emerald-700/20 dark:border-emerald-300/20',
                'toggleBorder' => 'border-emerald-700/50 dark:border-emerald-300/50!',
            ],
            self::Teal => [
                'bg' => 'bg-teal-500/20!',
                'border' => 'border-teal-700/40 dark:border-teal-600/40',
                'text' => 'text-teal-700 dark:text-teal-300',
                'subBorder' => 'border-teal-700/20 dark:border-teal-300/20',
                'toggleBorder' => 'border-teal-700/50 dark:border-teal-300/50!',
            ],
            self::Cyan => [
                'bg' => 'bg-cyan-500/20!',
                'border' => 'border-cyan-700/40 dark:border-cyan-600/40',
                'text' => 'text-cyan-700 dark:text-cyan-300',
                'subBorder' => 'border-cyan-700/20 dark:border-cyan-300/20',
                'toggleBorder' => 'border-cyan-700/50 dark:border-cyan-300/50!',
            ],
            self::Sky => [
                'bg' => 'bg-sky-500/20!',
                'border' => 'border-sky-700/40 dark:border-sky-600/40',
                'text' => 'text-sky-700 dark:text-sky-300',
                'subBorder' => 'border-sky-700/20 dark:border-sky-300/20',
                'toggleBorder' => 'border-sky-700/50 dark:border-sky-300/50!',
            ],
            self::Blue => [
                'bg' => 'bg-blue-500/20!',
                'border' => 'border-blue-700/40 dark:border-blue-600/40',
                'text' => 'text-blue-700 dark:text-blue-300',
                'subBorder' => 'border-blue-700/20 dark:border-blue-300/20',
                'toggleBorder' => 'border-blue-700/50 dark:border-blue-300/50!',
            ],
            self::Indigo => [
                'bg' => 'bg-indigo-500/20!',
                'border' => 'border-indigo-700/40 dark:border-indigo-600/40',
                'text' => 'text-indigo-700 dark:text-indigo-300',
                'subBorder' => 'border-indigo-700/20 dark:border-indigo-300/20',
                'toggleBorder' => 'border-indigo-700/50 dark:border-indigo-300/50!',
            ],
            self::Violet => [
                'bg' => 'bg-violet-500/20!',
                'border' => 'border-violet-700/40 dark:border-violet-600/40',
                'text' => 'text-violet-700 dark:text-violet-300',
                'subBorder' => 'border-violet-700/20 dark:border-violet-300/20',
                'toggleBorder' => 'border-violet-700/50 dark:border-violet-300/50!',
            ],
            self::Purple => [
                'bg' => 'bg-purple-500/20!',
                'border' => 'border-purple-700/40 dark:border-purple-600/40',
                'text' => 'text-purple-700 dark:text-purple-300',
                'subBorder' => 'border-purple-700/20 dark:border-purple-300/20',
                'toggleBorder' => 'border-purple-700/50 dark:border-purple-300/50!',
            ],
            self::Fuchsia => [
                'bg' => 'bg-fuchsia-500/20!',
                'border' => 'border-fuchsia-700/40 dark:border-fuchsia-600/40',
                'text' => 'text-fuchsia-700 dark:text-fuchsia-300',
                'subBorder' => 'border-fuchsia-700/20 dark:border-fuchsia-300/20',
                'toggleBorder' => 'border-fuchsia-700/50 dark:border-fuchsia-300/50!',
            ],
            self::Pink => [
                'bg' => 'bg-pink-500/20!',
                'border' => 'border-pink-700/40 dark:border-pink-600/40',
                'text' => 'text-pink-700 dark:text-pink-300',
                'subBorder' => 'border-pink-700/20 dark:border-pink-300/20',
                'toggleBorder' => 'border-pink-700/50 dark:border-pink-300/50!',
            ],
            self::Rose => [
                'bg' => 'bg-rose-500/20!',
                'border' => 'border-rose-700/40 dark:border-rose-600/40',
                'text' => 'text-rose-700 dark:text-rose-300',
                'subBorder' => 'border-rose-700/20 dark:border-rose-300/20',
                'toggleBorder' => 'border-rose-700/50 dark:border-rose-300/50!',
            ],
            self::Slate => [
                'bg' => 'bg-slate-100/60! dark:bg-slate-900/30!',
                'border' => 'border-slate-300 dark:border-slate-700',
                'text' => 'text-slate-900 dark:text-slate-100',
                'subBorder' => 'border-slate-200 dark:border-slate-800',
                'toggleBorder' => 'border-slate-400 dark:border-slate-600!',
            ],
            self::Gray => [
                'bg' => 'bg-gray-100/60! dark:bg-gray-900/30!',
                'border' => 'border-gray-300 dark:border-gray-700',
                'text' => 'text-gray-900 dark:text-gray-100',
                'subBorder' => 'border-gray-200 dark:border-gray-800',
                'toggleBorder' => 'border-gray-400 dark:border-gray-600!',
            ],
            self::Zinc => [
                'bg' => 'bg-zinc-100/60! dark:bg-zinc-900/30!',
                'border' => 'border-zinc-300 dark:border-zinc-700',
                'text' => 'text-zinc-900 dark:text-zinc-100',
                'subBorder' => 'border-zinc-200 dark:border-zinc-800',
                'toggleBorder' => 'border-zinc-400 dark:border-zinc-600!',
            ],
            self::Neutral => [
                'bg' => 'bg-neutral-100/60! dark:bg-neutral-900/30!',
                'border' => 'border-neutral-300 dark:border-neutral-700',
                'text' => 'text-neutral-900 dark:text-neutral-100',
                'subBorder' => 'border-neutral-200 dark:border-neutral-800',
                'toggleBorder' => 'border-neutral-400 dark:border-neutral-600!',
            ],
            self::Stone => [
                'bg' => 'bg-stone-100/60! dark:bg-stone-900/30!',
                'border' => 'border-stone-300 dark:border-stone-700',
                'text' => 'text-stone-900 dark:text-stone-100',
                'subBorder' => 'border-stone-200 dark:border-stone-800',
                'toggleBorder' => 'border-stone-400 dark:border-stone-600!',
            ],
            self::Mauve => [
                'bg' => 'bg-mauve-100/60! dark:bg-mauve-900/30!',
                'border' => 'border-mauve-300 dark:border-mauve-700',
                'text' => 'text-mauve-900 dark:text-mauve-100',
                'subBorder' => 'border-mauve-200 dark:border-mauve-800',
                'toggleBorder' => 'border-mauve-400 dark:border-mauve-600!',
            ],
            self::Olive => [
                'bg' => 'bg-olive-100/60! dark:bg-olive-900/30!',
                'border' => 'border-olive-300 dark:border-olive-700',
                'text' => 'text-olive-900 dark:text-olive-100',
                'subBorder' => 'border-olive-200 dark:border-olive-800',
                'toggleBorder' => 'border-olive-400 dark:border-olive-600!',
            ],
            self::Mist => [
                'bg' => 'bg-mist-100/60! dark:bg-mist-900/30!',
                'border' => 'border-mist-300 dark:border-mist-700',
                'text' => 'text-mist-900 dark:text-mist-100',
                'subBorder' => 'border-mist-200 dark:border-mist-800',
                'toggleBorder' => 'border-mist-400 dark:border-mist-600!',
            ],
            self::Taupe => [
                'bg' => 'bg-taupe-100/60! dark:bg-taupe-900/30!',
                'border' => 'border-taupe-300 dark:border-taupe-700',
                'text' => 'text-taupe-900 dark:text-taupe-100',
                'subBorder' => 'border-taupe-200 dark:border-taupe-800',
                'toggleBorder' => 'border-taupe-400 dark:border-taupe-600!',
            ],
        };
    }
}
