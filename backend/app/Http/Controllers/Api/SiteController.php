<?php

namespace App\Http\Controllers\Api;

use App\Models\Page;
use Illuminate\Http\JsonResponse;

class SiteController
{
    /** GET /api/v1/navigation — a simple menu built from published pages. */
    public function navigation(): JsonResponse
    {
        $items = Page::query()
            ->published()
            ->orderBy('title')
            ->get(['title', 'slug'])
            ->map(fn (Page $page): array => [
                'label' => $page->title,
                'to' => $page->slug === 'home' ? '/' : '/'.$page->slug,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    /** GET /api/v1/globals — site-wide settings the frontend needs on every page. */
    public function globals(): JsonResponse
    {
        return response()->json([
            'data' => [
                'siteName' => config('app.name'),
                'year' => (int) date('Y'),
            ],
        ]);
    }
}
