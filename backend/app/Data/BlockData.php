<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A single block in a page's content stream: a stable `type` string plus its
 * normalized `data` payload. The frontend BlockRenderer resolves `type` to a
 * Vue component, which narrows `data` to its specific block DTO type.
 */
#[TypeScript]
class BlockData extends Data
{
    public function __construct(
        public string $type,
        /** @var array<string, mixed> */
        public array $data,
    ) {}
}
