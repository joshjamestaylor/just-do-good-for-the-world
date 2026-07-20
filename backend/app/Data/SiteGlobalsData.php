<?php

namespace App\Data;

use App\Models\SiteSetting;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The `GET /api/v1/globals` payload — site-wide settings the frontend needs on
 * every page. This is the single contract for the brand palette, so the Filament
 * editor, the API JSON, and the Nuxt theme can never silently drift apart.
 */
#[TypeScript]
class SiteGlobalsData extends Data
{
    public function __construct(
        public string $siteName,
        public int $year,
        /** @var array<int, BrandColorData> */
        #[DataCollectionOf(BrandColorData::class)]
        public array $colors,
        public SemanticColorsData $semanticColors,
    ) {}

    public static function fromSettings(SiteSetting $settings): self
    {
        return self::from([
            'siteName' => config('app.name'),
            'year' => (int) date('Y'),
            'colors' => $settings->colors ?? [],
            'semanticColors' => $settings->semantic_colors ?? [],
        ]);
    }
}
