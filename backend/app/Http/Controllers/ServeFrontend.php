<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * INTEGRATED MODE: serve the statically-generated Nuxt output from Laravel so
 * the whole site runs on a single origin (Filament at /admin, API at /api, and
 * the frontend everywhere else). Registered as a route fallback, so it only
 * runs when no /admin or /api route matched.
 *
 * Build it first with:  cd frontend && npm run generate
 * (For local dev you'd usually just run `nuxt dev` on :3000 instead.)
 */
class ServeFrontend
{
    public function __invoke(Request $request): Response|BinaryFileResponse
    {
        $dist = realpath(base_path('../frontend/.output/public'));

        if ($dist === false) {
            abort(404, 'Frontend build not found. Run `npm run generate` in /frontend, or use `nuxt dev` for local development.');
        }

        $path = trim($request->path(), '/');

        // Try, in order: exact file, prerendered directory index, .html sibling.
        $candidates = array_filter([
            $path !== '' ? "{$dist}/{$path}" : null,
            $path !== '' ? "{$dist}/{$path}/index.html" : null,
            $path !== '' ? "{$dist}/{$path}.html" : null,
            $path === '' ? "{$dist}/index.html" : null,
        ]);

        foreach ($candidates as $file) {
            $real = realpath($file);

            // Confine every resolved path to the build directory (no traversal).
            if ($real !== false
                && is_file($real)
                && str_starts_with($real, $dist.DIRECTORY_SEPARATOR)) {
                return response()->file($real);
            }
        }

        // Client-only routes (e.g. /preview/*) aren't prerendered — serve the SPA
        // fallback with a 200 so the app boots and renders them, mirroring the
        // netlify.toml redirect. `nuxt generate` emits 200.html for exactly this.
        $spaFallback = is_file("{$dist}/200.html") ? "{$dist}/200.html" : "{$dist}/index.html";

        if (str_starts_with($path, 'preview/') && is_file($spaFallback)) {
            return response()->file($spaFallback);
        }

        // Otherwise: real 404 page if present, else SPA fallback, else bare 404.
        if (is_file("{$dist}/404.html")) {
            return response()->file("{$dist}/404.html")->setStatusCode(404);
        }

        if (is_file($spaFallback)) {
            return response()->file($spaFallback);
        }

        abort(404);
    }
}
