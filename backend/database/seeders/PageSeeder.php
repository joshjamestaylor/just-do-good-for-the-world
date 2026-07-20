<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home',
                'status' => PageStatus::Published,
                'published_at' => now(),
                'seo' => [
                    'title' => 'Decoupled Filament + Nuxt starter',
                    'description' => 'A page built from reusable blocks in Filament and rendered by Nuxt UI.',
                    'ogImage' => null,
                ],
                'content' => [
                    [
                        'type' => 'page_section',
                        'data' => [
                            'headline' => 'Decoupled CMS',
                            'title' => 'Author in Filament, render with Nuxt UI',
                            'description' => 'This whole section is a "page_section" block. Every field you see was set in the CMS and delivered as JSON — edit it in Filament and the frontend updates with no code change.',
                            'icon' => 'i-lucide-rocket',
                            'orientation' => 'horizontal',
                            'reverse' => false,
                            'links' => [
                                [
                                    'label' => 'See the About page',
                                    'to' => '/about',
                                    'icon' => null,
                                    'trailingIcon' => 'i-lucide-arrow-right',
                                    'color' => 'primary',
                                    'variant' => 'solid',
                                ],
                                [
                                    'label' => 'Nuxt UI Page Section',
                                    'href' => 'https://ui.nuxt.com/docs/components/page-section',
                                    'trailingIcon' => 'i-lucide-external-link',
                                    'color' => 'neutral',
                                    'variant' => 'outline',
                                ],
                            ],
                            'features' => [
                                [
                                    'icon' => 'i-lucide-blocks',
                                    'title' => 'Reusable blocks',
                                    'description' => 'Each block maps 1:1 to a Nuxt UI component.',
                                ],
                                [
                                    'icon' => 'i-lucide-unplug',
                                    'title' => 'Detachable',
                                    'description' => 'Run integrated with Laravel or as a static Netlify site.',
                                ],
                                [
                                    'icon' => 'i-lucide-git-compare-arrows',
                                    'title' => 'Type-safe contract',
                                    'description' => 'DTOs generate the frontend TypeScript types.',
                                ],
                            ],
                            'ui' => [],
                        ],
                    ],
                    [
                        'type' => 'page_section',
                        'data' => [
                            'title' => 'Add blocks without touching the frontend',
                            'description' => 'Run `php artisan make:block Hero`, drop a Hero.vue in the frontend, and the new block appears in the page builder.',
                            'orientation' => 'vertical',
                            'reverse' => false,
                            // Demonstrates the per-section background — references the
                            // "Mist" brand colour by name, resolved to its --brand-* CSS
                            // variable and baked into the prerendered HTML.
                            'backgroundColor' => 'Mist',
                            'links' => [],
                            'features' => [],
                            'ui' => [],
                        ],
                    ],
                ],
            ],
        );

        Page::updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'status' => PageStatus::Published,
                'published_at' => now(),
                'seo' => [
                    'title' => 'About this starter',
                    'description' => 'A second page — proving dynamic slug routes are enumerated and prerendered.',
                    'ogImage' => null,
                ],
                'content' => [
                    [
                        'type' => 'page_section',
                        'data' => [
                            'headline' => 'About',
                            'title' => 'A second, statically-prerendered page',
                            'description' => 'This page exists only in the CMS. At build time the Nuxt prerender hook enumerates it from the API and emits /about/index.html — no route was hand-written on the frontend.',
                            'orientation' => 'vertical',
                            'reverse' => false,
                            'links' => [
                                [
                                    'label' => 'Back home',
                                    'to' => '/',
                                    'icon' => 'i-lucide-arrow-left',
                                    'color' => 'neutral',
                                    'variant' => 'subtle',
                                ],
                            ],
                            'features' => [],
                            'ui' => [],
                        ],
                    ],
                ],
            ],
        );
    }
}
