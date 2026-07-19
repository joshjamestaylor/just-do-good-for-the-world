<?php

namespace App\Cms\Blocks;

use BackedEnum;
use Filament\Forms\Components\Builder\Block;

/**
 * Base class for a CMS page block.
 *
 * A block is defined ONCE here and flows three ways:
 *   1. Filament editing schema  (static::schema())
 *   2. Stable JSON {type, data} (persisted by the Filament Builder field)
 *   3. Nuxt component           (a matching .vue in frontend/app/components/blocks)
 *
 * The block `type()` string is the contract binding all three together, and
 * `dataClass()` points at the spatie/laravel-data DTO that both validates the
 * JSON shape and generates the frontend TypeScript type.
 */
abstract class PageBlock
{
    /** The stable block key, e.g. "page_section". Must match the Nuxt component registry key. */
    abstract public static function type(): string;

    /** Human label shown in the Filament block picker. */
    abstract public static function label(): string;

    /** FQCN of the spatie/laravel-data DTO describing this block's `data`. */
    abstract public static function dataClass(): string;

    /**
     * The Filament form components that make up this block's editable fields.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    abstract public static function schema(): array;

    /** Optional icon for the block picker (Heroicon enum or icon string). */
    public static function icon(): string|BackedEnum|null
    {
        return null;
    }

    /** Build the Filament Builder\Block instance from the pieces above. */
    public static function make(): Block
    {
        $block = Block::make(static::type())
            ->label(static::label())
            ->schema(static::schema());

        if ($icon = static::icon()) {
            $block->icon($icon);
        }

        return $block;
    }
}
