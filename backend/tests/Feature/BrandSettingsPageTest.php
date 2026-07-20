<?php

namespace Tests\Feature;

use App\Filament\Pages\BrandSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrandSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->create());
    }

    public function test_the_page_renders(): void
    {
        Livewire::test(BrandSettings::class)->assertOk();
    }

    public function test_saving_persists_the_palette_to_the_single_settings_row(): void
    {
        Livewire::test(BrandSettings::class)
            ->fillForm([
                'colors' => [
                    ['name' => 'Primary Blue', 'hex' => '#0057FF', 'role' => 'primary'],
                    ['name' => 'Warm Yellow', 'hex' => '#FFD54A', 'role' => 'accent'],
                ],
                'semantic_colors' => ['enabled' => true, 'success' => '#2ECC71'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = SiteSetting::current();

        $this->assertCount(1, SiteSetting::all());
        $this->assertSame('#0057FF', $settings->colors[0]['hex']);
        $this->assertSame('primary', $settings->colors[0]['role']);
        $this->assertTrue($settings->semantic_colors['enabled']);
        $this->assertSame('#2ECC71', $settings->semantic_colors['success']);
    }
}
