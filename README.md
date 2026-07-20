# Decoupled Filament CMS + Nuxt UI frontend

A headless-capable CMS in one repo: a **Laravel + Filament v5** admin authors content through a
**page builder of reusable blocks**, and a **Nuxt 4 + Nuxt UI v4** frontend renders those blocks. The two
halves talk **only** through a versioned **JSON API**, so the frontend can run **integrated** (served by
Laravel) or **detached** (a fully static site on Netlify) — switched by a single environment variable, with no
code changes.

```
Filament block schema  ─►  JSON  { "type": "page_section", "data": {…} }  ─►  Nuxt <UPageSection/>
   (edit fields)                    (the stable contract)                       (render)
```

Every block maps 1:1 to a Nuxt UI component; every editable field maps to one of that component's props. The
reference block is [`UPageSection`](https://ui.nuxt.com/docs/components/page-section).

---

## Repository layout

```
backend/    Laravel 13 + Filament v5 — the CMS admin + JSON API  (SQLite by default)
frontend/   Nuxt 4 + Nuxt UI v4 — self-contained, deployable on its own
```

Key files:

| Concern | Path |
|---|---|
| Block base class + auto-discovery | [backend/app/Cms/Blocks/PageBlock.php](backend/app/Cms/Blocks/PageBlock.php), [BlockRegistry.php](backend/app/Cms/Blocks/BlockRegistry.php) |
| Reference block | [backend/app/Cms/Blocks/PageSectionBlock.php](backend/app/Cms/Blocks/PageSectionBlock.php) |
| Block data DTOs (JSON + TS source) | [backend/app/Data/](backend/app/Data/) |
| JSON serializer | [backend/app/Support/BlockSerializer.php](backend/app/Support/BlockSerializer.php) |
| Page builder form | [backend/app/Filament/Resources/Pages/Schemas/PageForm.php](backend/app/Filament/Resources/Pages/Schemas/PageForm.php) |
| API routes / controllers | [backend/routes/api.php](backend/routes/api.php), [backend/app/Http/Controllers/Api/](backend/app/Http/Controllers/Api/) |
| Frontend API gateway | [frontend/app/composables/useApi.ts](frontend/app/composables/useApi.ts) |
| Block renderer + registry | [frontend/app/components/BlockRenderer.vue](frontend/app/components/BlockRenderer.vue) |
| Block components | [frontend/app/components/blocks/](frontend/app/components/blocks/) |
| Catch-all page | [frontend/app/pages/[...slug].vue](frontend/app/pages/%5B...slug%5D.vue) |
| Generated shared types | [frontend/types/api.ts](frontend/types/api.ts) |

---

## Prerequisites

PHP 8.3+, Composer, Node 20+, npm. No external database needed (SQLite).

## Quick start (local dev)

```bash
# 1. Backend (CMS + API) — http://localhost:8000
cd backend
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed         # creates schema + a Home & About page + an admin user
php artisan serve                  # http://localhost:8000  (admin at /admin)

# 2. Frontend (in a second terminal) — http://localhost:3000
cd frontend
npm install
npm run dev
```

- Admin panel: <http://localhost:8000/admin> — log in with **admin@example.com** / **password**.
- Site: <http://localhost:3000>.

Edit the Home page's Page Section block in Filament, save, refresh the frontend — the change appears with no
frontend edit.

---

## Integrated vs detached (the one env var)

The frontend reads its API location from `NUXT_PUBLIC_API_BASE` (→ `runtimeConfig.public.apiBase`). That single
value is the switch:

| Mode | Who serves the frontend | `NUXT_PUBLIC_API_BASE` | Applied |
|---|---|---|---|
| **Integrated** | Laravel (`Route::fallback` serves the built Nuxt output) | `http://localhost:8000/api/v1` | runtime (SSR/dev) |
| **Detached** | Netlify (static `.output/public`) | `https://cms.example.com/api/v1` | build time (`nuxt generate`) |

**Detached static build:**

```bash
cd frontend
NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1 npm run generate   # → .output/public
```

The build hooks Nitro's `nitro:config` to fetch `/pages` from the API and prerender every slug to a static file
(see [frontend/nuxt.config.ts](frontend/nuxt.config.ts)).

**Integrated single-origin:** after `npm run generate`, Laravel's fallback route serves `.output/public`, so
`http://localhost:8000/` shows the site while `/admin` and `/api` keep working
(see [backend/app/Http/Controllers/ServeFrontend.php](backend/app/Http/Controllers/ServeFrontend.php)).

---

## Adding a new block

Two files, no wiring — both sides auto-discover.

```bash
cd backend
php artisan make:block Hero          # creates HeroBlock.php + HeroData.php (auto-registered)
# 1. Add fields to app/Cms/Blocks/HeroBlock.php::schema()
# 2. Mirror them as properties on app/Data/Blocks/HeroData.php
# 3. Create frontend/app/components/blocks/Hero.vue  (maps HeroData -> a Nuxt UI component)
php artisan typescript:transform     # refresh frontend/types/api.ts
```

**Naming convention:** the Vue file's PascalCase name maps to the block `type` in snake_case
(`PageSection.vue` ↔ `page_section`). An unknown block type renders a visible warning rather than breaking.

---

## JSON API

Base: `/api/v1`. All responses are wrapped in a `data` envelope.

| Method | Path | Purpose |
|---|---|---|
| GET | `/pages` | Published pages (id, slug, title, updatedAt) — nav + prerender enumeration |
| GET | `/pages/{slug}` | Full page: seo + ordered `blocks` (published only) |
| GET | `/navigation` | Menu built from published pages |
| GET | `/globals` | Site-wide settings — site name + the brand colour palette |
| GET | `/preview/pages/{slug}` | Draft-inclusive; requires a valid signed URL |

## Global brand colours

Site-wide colours are edited in Filament at **`/admin` → Settings → Brand** (`/admin/brand-settings`),
stored in a single [`SiteSetting`](backend/app/Models/SiteSetting.php) row, and served on `/globals`. The
model is deliberately flexible rather than a fixed Primary/Secondary/Tertiary: a brand has an **unlimited,
ordered list** of colours, each with a `name`, a `hex`, and an **optional `role`**
([`ColorRole`](backend/app/Enums/ColorRole.php): `primary`, `secondary`, `accent`, `neutral`, `background`,
`text`). So a 2-colour and a 10-colour brand share one shape.

```json
"colors": [
  { "name": "Brand Green", "hex": "#00C16A", "role": "primary" },
  { "name": "Slate",       "hex": "#64748B", "role": "neutral" },
  { "name": "Sunset Orange", "hex": "#F97316", "role": "accent" }
]
```

**Status colours are separate.** Success / warning / error / info are a UI concept, not a branding one, so
they default to conventional, accessible colours (green/amber/red/blue — provided by Nuxt UI's built-in
aliases) and are only overridden when an editor flips `semanticColors.enabled` on.

**How it themes the static site.** The frontend turns the palette into CSS custom properties
([`buildBrandThemeCss`](frontend/app/utils/brandTheme.ts)) injected as an inline `<style>` in
[`app.vue`](frontend/app/app.vue) — so it's **baked into every prerendered page** and the detached static
build carries its own theme with no runtime API call. Roles that map to a Nuxt UI alias (`primary`,
`secondary`, `neutral`) re-theme components automatically (shades are derived from the one stored hex via
`color-mix()`); every colour is also exposed as `--brand-<name>` / `--brand-<role>` for content to use.

## Draft & preview

Pages have a `status` (draft/published) + `published_at`; public endpoints return published pages only. The
Filament pages table has a **Preview** action that opens `FRONTEND_URL/preview/{slug}` carrying a temporary
signed token, which the frontend forwards to the token-guarded preview endpoint.

> The preview signature is computed over the absolute API URL, so **`APP_URL` must match the origin the frontend
> targets** via `NUXT_PUBLIC_API_BASE` (e.g. both `localhost`, not one `localhost` and one `127.0.0.1`).

## Type-safe contract

Block/page DTOs are `spatie/laravel-data` classes annotated `#[TypeScript]`. `php artisan typescript:transform`
writes them to `frontend/types/api.ts`, which the Vue components import. Commit `api.ts`; in CI, regenerate and
`git diff --exit-code frontend/types/api.ts` to fail on drift.

## Deploy to Netlify (detached)

Point Netlify at this repo, set the site's **Base directory** to `frontend`, and set `NUXT_PUBLIC_API_BASE` to
your public API. Build config is in [frontend/netlify.toml](frontend/netlify.toml) (`netlify-static` preset,
`npm run generate`). To rebuild on publish, add a Netlify build hook and call it from a Filament `Page` model
observer (deferred — not built in this starter).

---

## Going to production

- **Lock down the admin.** `User::canAccessPanel` allows everyone in `local`/`testing` but in production only
  emails in `ADMIN_EMAILS` (comma-separated) — an empty list denies everyone. Set it before deploying.
- **Change the seeded admin.** The default `admin@example.com` / `password` is dev-only; override with
  `ADMIN_EMAIL` / `ADMIN_PASSWORD`, or don't run the seeder in production.
- **Tighten CORS** to your frontend origin via `CORS_ALLOWED_ORIGINS` (defaults to `*`).
- **Match origins for preview.** `APP_URL` must equal the origin the frontend calls; leave `FRONTEND_URL` unset
  in integrated mode (it falls back to `APP_URL`).

## What's included vs deferred

**Included:** Filament v5 page builder, the Page Section block end-to-end, JSON API, integrated/detached switch,
static generation, draft + signed preview, generated TS types, `make:block` generator, editable global brand
colours (baked into the static build).

**Architected for but not built:** media library (blocks currently use text/icon/link fields), automatic
Netlify rebuild-on-publish webhook, and i18n/multilingual.
