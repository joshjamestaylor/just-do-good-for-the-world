<?php

use App\Http\Controllers\ServeFrontend;
use Illuminate\Support\Facades\Route;

/*
| INTEGRATED MODE — serve the built Nuxt frontend from Laravel.
|
| This is a fallback: it only runs when nothing else matched, so Filament
| (/admin) and the JSON API (/api/*) keep working. In detached mode this route
| is simply never hit — Netlify serves the frontend instead. In local dev you
| typically run `nuxt dev` on :3000 and ignore this entirely.
*/
Route::fallback(ServeFrontend::class);
