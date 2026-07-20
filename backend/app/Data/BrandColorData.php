<?php

namespace App\Data;

use App\Enums\ColorRole;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One colour in a brand's palette. A brand has an unlimited, ordered list of
 * these (order = the array index, controlled by drag-reorder in Filament).
 *
 * `name` is what the client controls ("Sunset Orange"); `role` is the optional
 * semantic hint the application uses to apply the colour. Avoiding a fixed
 * Primary/Secondary/Tertiary model lets a 2-colour and a 10-colour brand share
 * the same shape.
 */
#[TypeScript]
class BrandColorData extends Data
{
    public function __construct(
        public string $name = '',
        /** Hex string, e.g. "#0057FF". */
        public string $hex = '#000000',
        public ?ColorRole $role = null,
    ) {}
}
