<?php

namespace App\Data\Blocks;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/** Maps to a Nuxt UI PageFeatureProps item (the shape of each UPageSection `features` entry). */
#[TypeScript]
class FeatureData extends Data
{
    public function __construct(
        public ?string $icon = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $to = null,
    ) {}
}
