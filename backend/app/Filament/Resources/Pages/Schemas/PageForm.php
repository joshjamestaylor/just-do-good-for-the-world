<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Cms\Blocks\BlockRegistry;
use App\Enums\PageStatus;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('URL path segment, e.g. "about". Use "home" for the site root.'),
                    Select::make('status')
                        ->options(PageStatus::class)
                        ->default(PageStatus::Draft)
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->required(),
                    DateTimePicker::make('published_at')
                        ->label('Publish at')
                        ->helperText('Page is public only once this time has passed.'),
                ])
                ->columnSpanFull(),

            Section::make('SEO')
                ->columns(2)
                ->collapsed()
                ->schema([
                    TextInput::make('seo.title')->label('SEO title'),
                    TextInput::make('seo.ogImage')->label('OG image URL')->url(),
                    Textarea::make('seo.description')
                        ->label('SEO description')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Builder::make('content')
                ->label('Content blocks')
                ->blocks(BlockRegistry::filamentBlocks())
                ->blockNumbers(false)
                ->collapsible()
                ->collapsed()
                ->cloneable()
                ->reorderable()
                ->addActionLabel('Add block')
                ->columnSpanFull(),
        ]);
    }
}
