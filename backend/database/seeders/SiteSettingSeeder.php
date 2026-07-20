<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        // A minimal starter palette. `primary` (Nuxt green) + `neutral` (slate)
        // reproduce the frontend's default theme, so the site looks identical
        // until an editor changes a colour — then components re-theme with no
        // frontend edit. Semantic colours stay on their conventional defaults.
        SiteSetting::current()->update([
            'colors' => [
                ['name' => 'Brand Green', 'hex' => '#00C16A', 'role' => 'primary'],
                ['name' => 'Slate', 'hex' => '#64748B', 'role' => 'neutral'],
                ['name' => 'Sunset Orange', 'hex' => '#F97316', 'role' => 'accent'],
                ['name' => 'Mist', 'hex' => '#F1F5F9', 'role' => 'background'],
            ],
            'semantic_colors' => [
                'enabled' => false,
                'success' => null,
                'warning' => null,
                'error' => null,
                'info' => null,
            ],
        ]);
    }
}
