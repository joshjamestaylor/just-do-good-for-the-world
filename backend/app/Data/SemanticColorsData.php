<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * UI status colours (success/warning/error/info). These are a *UI* concept, not
 * a *branding* one — most brand guidelines don't define them — so they default to
 * conventional, accessible colours (green/amber/red/blue, provided by Nuxt UI's
 * built-in aliases) and are only overridden when `enabled` is true. That keeps
 * onboarding simple while still letting e.g. a bank use teal for "success".
 */
#[TypeScript]
class SemanticColorsData extends Data
{
    public function __construct(
        /** When false, the frontend uses its conventional defaults and ignores the overrides below. */
        public bool $enabled = false,
        public ?string $success = null,
        public ?string $warning = null,
        public ?string $error = null,
        public ?string $info = null,
    ) {}
}
