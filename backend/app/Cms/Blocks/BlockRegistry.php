<?php

namespace App\Cms\Blocks;

use Filament\Forms\Components\Builder\Block;
use ReflectionClass;

/**
 * Auto-discovers every PageBlock subclass in app/Cms/Blocks.
 *
 * Adding a new block is therefore just "drop a *Block.php class in this folder" —
 * no registration, no config. The Filament Builder field and the JSON serializer
 * both read from here so they can never drift out of sync.
 */
class BlockRegistry
{
    /** @var array<int, class-string<PageBlock>>|null */
    protected static ?array $cache = null;

    /** @return array<int, class-string<PageBlock>> */
    public static function classes(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $classes = [];

        foreach (glob(app_path('Cms/Blocks/*Block.php')) ?: [] as $file) {
            $class = 'App\\Cms\\Blocks\\'.pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            if ($ref->isAbstract() || ! $ref->isSubclassOf(PageBlock::class)) {
                continue;
            }

            $classes[] = $class;
        }

        sort($classes);

        return static::$cache = $classes;
    }

    /** @return array<int, Block> The Filament blocks to hand to Builder::blocks(). */
    public static function filamentBlocks(): array
    {
        return array_map(static fn (string $class): Block => $class::make(), static::classes());
    }

    /**
     * Map of block type => Data DTO class, used by the JSON serializer.
     *
     * @return array<string, class-string>
     */
    public static function dataClasses(): array
    {
        $map = [];

        foreach (static::classes() as $class) {
            $map[$class::type()] = $class::dataClass();
        }

        return $map;
    }
}
