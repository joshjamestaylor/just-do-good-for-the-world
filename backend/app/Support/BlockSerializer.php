<?php

namespace App\Support;

use App\Cms\Blocks\BlockRegistry;
use App\Data\BlockData;

/**
 * Turns the raw Filament Builder JSON (an ordered array of {type, data}) into a
 * normalized array of BlockData, routing each block's `data` through its DTO so
 * the API output is validated and defaulted consistently.
 */
class BlockSerializer
{
    /**
     * @param  array<int, array{type?: string, data?: array<string, mixed>}>  $content
     * @return array<int, BlockData>
     */
    public function serialize(?array $content): array
    {
        $map = BlockRegistry::dataClasses();
        $blocks = [];

        foreach ($content ?? [] as $item) {
            $type = $item['type'] ?? null;

            if (! $type) {
                continue;
            }

            $raw = $item['data'] ?? [];
            $dataClass = $map[$type] ?? null;

            // Normalize through the block's DTO when we know it; otherwise pass raw.
            $data = $dataClass
                ? $dataClass::from($raw)->toArray()
                : $raw;

            $blocks[] = new BlockData(type: $type, data: $data);
        }

        return $blocks;
    }
}
