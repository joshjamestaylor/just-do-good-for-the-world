<?php

namespace App\Data;

use App\Models\Page;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/** Lightweight page summary for the index endpoint (navigation + prerender enumeration). */
#[TypeScript]
class PageListItemData extends Data
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public ?string $updatedAt,
    ) {}

    public static function fromPage(Page $page): self
    {
        return new self(
            id: $page->id,
            slug: $page->slug,
            title: $page->title,
            updatedAt: $page->updated_at?->toIso8601String(),
        );
    }
}
