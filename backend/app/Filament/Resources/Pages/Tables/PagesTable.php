<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Support\PreviewUrl;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            // Drag-to-reorder writes the row's position into `menu_order`, which
            // drives the public nav order (SiteController::navigation). Default
            // the list to that column so the admin table mirrors the live menu.
            ->reorderable('menu_order')
            ->defaultSort('menu_order')
            ->recordActions([
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn ($record): string => PreviewUrl::for($record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
