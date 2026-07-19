<?php

use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('navigation', [SiteController::class, 'navigation'])->name('navigation');
    Route::get('globals', [SiteController::class, 'globals'])->name('globals');

    // Draft preview — must be declared before the {slug} catch-all, and is
    // protected by a temporary signed URL (see App\Support\PreviewUrl).
    Route::get('preview/pages/{slug}', [PageController::class, 'preview'])
        ->middleware('signed')
        ->name('pages.preview');

    Route::get('pages/{slug}', [PageController::class, 'show'])->name('pages.show');
});
