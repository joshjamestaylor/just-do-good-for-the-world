<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSectionBackgroundTest extends TestCase
{
    use RefreshDatabase;

    public function test_section_background_fields_are_exposed_with_sensible_defaults(): void
    {
        Page::create([
            'slug' => 'colours',
            'title' => 'Colours',
            'status' => PageStatus::Published,
            'published_at' => now(),
            'content' => [
                ['type' => 'page_section', 'data' => [
                    'title' => 'Tinted + image',
                    // A brand-colour *name* (resolved to --brand-<slug> on the frontend).
                    'backgroundColor' => 'Sunset Orange',
                    'backgroundImage' => 'page-backgrounds/hero.jpg',
                    'backgroundPosition' => 'left',
                ]],
                ['type' => 'page_section', 'data' => ['title' => 'Plain']],
            ],
        ]);

        $response = $this->getJson('/api/v1/pages/colours')->assertOk();

        // Colour reference + position pass through; the image path becomes an absolute URL.
        $response
            ->assertJsonPath('data.blocks.0.data.backgroundColor', 'Sunset Orange')
            ->assertJsonPath('data.blocks.0.data.backgroundPosition', 'left');

        $this->assertStringContainsString(
            '/storage/page-backgrounds/hero.jpg',
            $response->json('data.blocks.0.data.backgroundImage'),
        );
        $this->assertStringStartsWith('http', $response->json('data.blocks.0.data.backgroundImage'));

        // Defaults when unset: no image, no colour, centred position.
        $response
            ->assertJsonPath('data.blocks.1.data.backgroundColor', null)
            ->assertJsonPath('data.blocks.1.data.backgroundImage', null)
            ->assertJsonPath('data.blocks.1.data.backgroundPosition', 'normal');
    }
}
