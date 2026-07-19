<?php

namespace App\Http\Controllers\Api;

use App\Data\PageData;
use App\Data\PageListItemData;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController
{
    /** GET /api/v1/pages — published pages (navigation + prerender enumeration). */
    public function index(): JsonResponse
    {
        $pages = Page::query()->published()->orderBy('title')->get();

        return response()->json([
            'data' => $pages->map(fn (Page $page) => PageListItemData::fromPage($page)),
        ]);
    }

    /** GET /api/v1/pages/{slug} — a single published page. */
    public function show(string $slug): JsonResponse
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => PageData::fromPage($page)]);
    }

    /**
     * GET /api/v1/preview/pages/{slug} — draft-inclusive content.
     * Access is gated by the `signed` middleware (temporary signed URL).
     */
    public function preview(string $slug): JsonResponse
    {
        $page = Page::query()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => PageData::fromPage($page)]);
    }
}
