<?php

namespace App\Data\Blocks;

use App\Data\Transformers\MediaUrlTransformer;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The `data` payload of a `page_section` block.
 *
 * Property names & types mirror the props of Nuxt UI's <UPageSection>. This DTO
 * is the single source of truth for the JSON shape AND the generated TS type
 * (frontend/types/api.d.ts), so the Filament fields, the API output, and the
 * Nuxt component can never silently drift apart.
 */
#[TypeScript]
class PageSectionData extends Data
{
    public function __construct(
        public string $title = '',
        public ?string $headline = null,
        public ?string $description = null,
        public ?string $icon = null,
        public string $orientation = 'vertical',
        public bool $reverse = false,
        /** Absolute URL in API output; a disk-relative path is stored by Filament. */
        #[WithTransformer(MediaUrlTransformer::class)]
        public ?string $image = null,
        /** @var array<int, LinkData> */
        #[DataCollectionOf(LinkData::class)]
        public array $links = [],
        /** @var array<int, FeatureData> */
        #[DataCollectionOf(FeatureData::class)]
        public array $features = [],
        /** @var array<string, string> */
        public array $ui = [],
    ) {}
}
