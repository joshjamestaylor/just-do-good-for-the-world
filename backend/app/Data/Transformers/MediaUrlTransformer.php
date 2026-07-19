<?php

namespace App\Data\Transformers;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

/**
 * Turns a stored media path (what Filament's FileUpload persists, e.g.
 * "page-sections/hero.jpg") into a full, absolute URL for the API — so the
 * frontend can render it directly and the snapshot build can download it.
 *
 * Idempotent: values that are already absolute URLs are passed through
 * unchanged (e.g. re-serialized output or externally-hosted media).
 */
class MediaUrlTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // The public disk URL is root-relative ("/storage/…") so the Filament admin
        // loads previews same-origin (see config/filesystems.php). The API is consumed
        // cross-origin by the decoupled frontend, so prepend the app origin to make it
        // absolute — keeping the API output identical to an absolute-disk-URL setup.
        return rtrim(config('app.url'), '/').Storage::disk('public')->url($value);
    }
}
