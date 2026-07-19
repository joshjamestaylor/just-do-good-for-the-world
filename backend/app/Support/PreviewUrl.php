<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\URL;

/**
 * Builds the frontend preview URL for a page. It embeds the signed query
 * (expires + signature) of the API preview route so the frontend can call the
 * token-guarded /api/v1/preview/pages/{slug} endpoint and see draft content.
 *
 * The signature is computed over the absolute API URL, so APP_URL must be the
 * same origin the frontend targets via NUXT_PUBLIC_API_BASE.
 */
class PreviewUrl
{
    public static function for(Page $page): string
    {
        $signed = URL::temporarySignedRoute(
            'api.v1.pages.preview',
            now()->addHour(),
            ['slug' => $page->slug],
        );

        $query = parse_url($signed, PHP_URL_QUERY);
        $frontend = rtrim((string) config('app.frontend_url'), '/');

        return "{$frontend}/preview/{$page->slug}?{$query}";
    }
}
