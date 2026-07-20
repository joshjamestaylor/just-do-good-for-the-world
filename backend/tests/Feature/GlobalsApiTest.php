<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_globals_returns_defaults_when_no_settings_exist(): void
    {
        $this->getJson('/api/v1/globals')
            ->assertOk()
            ->assertJsonPath('data.siteName', config('app.name'))
            ->assertJsonPath('data.colors', [])
            ->assertJsonPath('data.semanticColors.enabled', false);

        // The endpoint lazily materialises the single settings row.
        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_globals_exposes_the_brand_palette(): void
    {
        SiteSetting::current()->update([
            'colors' => [
                ['name' => 'Primary Blue', 'hex' => '#0057FF', 'role' => 'primary'],
                ['name' => 'Warm Yellow', 'hex' => '#FFD54A', 'role' => 'accent'],
            ],
            'semantic_colors' => ['enabled' => true, 'success' => '#2ECC71'],
        ]);

        $this->getJson('/api/v1/globals')
            ->assertOk()
            ->assertJsonPath('data.colors.0.name', 'Primary Blue')
            ->assertJsonPath('data.colors.0.hex', '#0057FF')
            ->assertJsonPath('data.colors.0.role', 'primary')
            ->assertJsonPath('data.colors.1.role', 'accent')
            ->assertJsonPath('data.semanticColors.enabled', true)
            ->assertJsonPath('data.semanticColors.success', '#2ECC71')
            ->assertJsonPath('data.semanticColors.warning', null);
    }
}
