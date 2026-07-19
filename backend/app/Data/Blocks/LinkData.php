<?php

namespace App\Data\Blocks;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/** Maps to a Nuxt UI ButtonProps item (the shape of each UPageSection `links` entry). */
#[TypeScript]
class LinkData extends Data
{
    public function __construct(
        public ?string $label = null,
        public ?string $to = null,
        public ?string $href = null,
        public ?string $icon = null,
        public ?string $trailingIcon = null,
        public string $color = 'primary',
        public string $variant = 'solid',
    ) {}
}
