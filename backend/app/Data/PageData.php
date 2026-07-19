<?php

namespace App\Data;

use App\Models\Page;
use App\Support\BlockSerializer;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/** The full page contract consumed by the frontend for a single route. */
#[TypeScript]
class PageData extends Data
{
    public function __construct(
        public string $slug,
        public string $title,
        public ?SeoData $seo,
        /** @var array<int, BlockData> */
        public array $blocks,
        public ?string $updatedAt,
    ) {}

    public static function fromPage(Page $page): self
    {
        return new self(
            slug: $page->slug,
            title: $page->title,
            seo: $page->seo ? SeoData::from($page->seo) : null,
            blocks: app(BlockSerializer::class)->serialize($page->content ?? []),
            updatedAt: $page->updated_at?->toIso8601String(),
        );
    }
}
