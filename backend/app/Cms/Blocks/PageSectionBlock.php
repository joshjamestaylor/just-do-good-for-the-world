<?php

namespace App\Cms\Blocks;

use App\Data\Blocks\PageSectionData;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

/**
 * Reference block. Its fields map 1:1 to the props of Nuxt UI's <UPageSection>:
 * https://ui.nuxt.com/docs/components/page-section
 *
 * UPageSection props: headline, icon, title, description, links (ButtonProps[]),
 * features (PageFeatureProps[]), orientation, reverse, class, ui. It has no
 * section-level color/variant — color/variant live on each individual link.
 */
class PageSectionBlock extends PageBlock
{
    public static function type(): string
    {
        return 'page_section';
    }

    public static function label(): string
    {
        return 'Page Section';
    }

    public static function dataClass(): string
    {
        return PageSectionData::class;
    }

    public static function icon(): string|\BackedEnum|null
    {
        return Heroicon::OutlinedRectangleGroup;
    }

    public static function schema(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->columnSpanFull(),
            TextInput::make('headline')
                ->helperText('Small eyebrow text shown above the title'),
            TextInput::make('icon')
                ->placeholder('i-lucide-rocket')
                ->helperText('Nuxt UI / Iconify icon id'),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            Select::make('orientation')
                ->options(['vertical' => 'Vertical', 'horizontal' => 'Horizontal'])
                ->default('vertical')
                ->native(false),
            Toggle::make('reverse')
                ->helperText('Swap the text / media order (horizontal only)'),
            ColorPicker::make('backgroundColor')
                ->label('Background colour')
                ->helperText('Optional colour band behind the whole section. Empty = transparent. Note: a fixed hex does not change between light/dark mode — for a background that follows the theme, use a Nuxt UI class (e.g. "bg-muted") in the UI overrides below.'),
            FileUpload::make('backgroundImage')
                ->label('Background image')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('page-backgrounds')
                ->visibility('public')
                ->helperText('Optional full-bleed image behind the section content (sits under the background colour).'),
            Select::make('backgroundPosition')
                ->label('Background image position')
                ->options(['normal' => 'Normal (centre)', 'left' => 'Left', 'right' => 'Right'])
                ->default('normal')
                ->selectablePlaceholder(false)
                ->native(false)
                ->visible(fn (Get $get): bool => filled($get('backgroundImage'))),
            FileUpload::make('image')
                ->label('Image')
                ->image()
                ->imageEditor()
                // Stored on the public disk so it is reachable at APP_URL/storage/…;
                // the snapshot build downloads it into the static site's assets.
                ->disk('public')
                ->directory('page-sections')
                ->visibility('public')
                ->helperText('Shown as the section media (alongside the text)')
                ->columnSpanFull(),
            Repeater::make('links')
                ->schema([
                    TextInput::make('label')->required(),
                    TextInput::make('to')->placeholder('/docs')->helperText('Internal path'),
                    TextInput::make('icon')->placeholder('i-lucide-rocket'),
                    TextInput::make('trailingIcon')->placeholder('i-lucide-arrow-right'),
                    Select::make('color')->options(self::colorOptions())->default('primary')->native(false),
                    Select::make('variant')->options(self::variantOptions())->default('solid')->native(false),
                ])
                ->columns(2)
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                ->defaultItems(0)
                ->columnSpanFull(),
            Repeater::make('features')
                ->schema([
                    TextInput::make('icon')->placeholder('i-lucide-bolt'),
                    TextInput::make('title'),
                    Textarea::make('description')->rows(2)->columnSpanFull(),
                    TextInput::make('to')->placeholder('/features'),
                ])
                ->columns(2)
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                ->defaultItems(0)
                ->columnSpanFull(),
            KeyValue::make('ui')
                ->label('UI overrides (advanced)')
                ->keyLabel('slot')
                ->valueLabel('classes')
                ->helperText('Map a Nuxt UI slot (e.g. "title") to Tailwind classes')
                ->columnSpanFull(),
        ];
    }

    /** Nuxt UI colors. @return array<string, string> */
    private static function colorOptions(): array
    {
        return [
            'primary' => 'Primary',
            'secondary' => 'Secondary',
            'success' => 'Success',
            'info' => 'Info',
            'warning' => 'Warning',
            'error' => 'Error',
            'neutral' => 'Neutral',
        ];
    }

    /** Nuxt UI button variants. @return array<string, string> */
    private static function variantOptions(): array
    {
        return [
            'solid' => 'Solid',
            'outline' => 'Outline',
            'soft' => 'Soft',
            'subtle' => 'Subtle',
            'ghost' => 'Ghost',
            'link' => 'Link',
        ];
    }
}
