<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The optional semantic role of a brand colour. `role` (not position) tells the
 * frontend how to *use* a colour: `primary`/`secondary`/`neutral` are mapped onto
 * Nuxt UI's theme so components pick them up automatically, while `accent`/
 * `background`/`text` are exposed as `--brand-*` CSS variables for content.
 *
 * Roles are intentionally optional and non-exclusive here — a brand can have zero
 * or many of any role. The UI (Filament) is where "exactly one primary" style
 * constraints belong; the model stays flexible so 2-colour and 10-colour brands
 * both fit.
 */
#[TypeScript]
enum ColorRole: string implements HasLabel
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Accent = 'accent';
    case Neutral = 'neutral';
    case Background = 'background';
    case Text = 'text';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
