<?php

namespace App\Filament\Pages;

use App\Enums\ColorRole;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Edits the site-wide brand palette (the `/api/v1/globals` payload). It's a
 * single-record settings screen: {@see SiteSetting} has exactly one row, so there
 * is no list/create — mount() fills the form from it and save() writes it back.
 *
 * @property-read Schema $form
 */
class BrandSettings extends Page
{
    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Brand';

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'colors' => $settings->colors ?? [],
            'semantic_colors' => $settings->semantic_colors ?? ['enabled' => false],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Brand colours')
                    ->description('The client-owned palette. Add as many as the brand needs; drag to reorder. A colour\'s role tells the site how to use it — primary, secondary and neutral drive the theme; the rest are available to content.')
                    ->schema([
                        Repeater::make('colors')
                            ->hiddenLabel()
                            ->addActionLabel('Add colour')
                            ->reorderable()
                            ->columns(3)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. Sunset Orange'),
                                ColorPicker::make('hex')
                                    ->label('Colour')
                                    ->required(),
                                Select::make('role')
                                    ->options(ColorRole::class)
                                    ->placeholder('No role')
                                    ->native(false)
                                    ->helperText('Optional semantic role.'),
                            ]),
                    ]),

                Section::make('Status colours')
                    ->description('Success / warning / error / info are UI concepts, not branding. They default to conventional, accessible colours — only override them if the brand has its own.')
                    ->schema([
                        Toggle::make('semantic_colors.enabled')
                            ->label('Override the default status colours')
                            ->live(),
                        ColorPicker::make('semantic_colors.success')
                            ->label('Success')
                            ->placeholder('#22C55E')
                            ->visible(fn (Get $get): bool => (bool) $get('semantic_colors.enabled')),
                        ColorPicker::make('semantic_colors.warning')
                            ->label('Warning')
                            ->placeholder('#F59E0B')
                            ->visible(fn (Get $get): bool => (bool) $get('semantic_colors.enabled')),
                        ColorPicker::make('semantic_colors.error')
                            ->label('Error')
                            ->placeholder('#EF4444')
                            ->visible(fn (Get $get): bool => (bool) $get('semantic_colors.enabled')),
                        ColorPicker::make('semantic_colors.info')
                            ->label('Info')
                            ->placeholder('#3B82F6')
                            ->visible(fn (Get $get): bool => (bool) $get('semantic_colors.enabled')),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::current()->update([
            'colors' => $data['colors'] ?? [],
            'semantic_colors' => $data['semantic_colors'] ?? ['enabled' => false],
        ]);

        Notification::make()
            ->success()
            ->title('Brand colours saved')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Save')
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                ])->key('form-actions'),
            ]);
    }
}
