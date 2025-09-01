<?php

namespace App\Filament\Resources\Tools\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class ToolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('serial')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('category')->toggleable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('power_watts')->numeric()->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('size')->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('updated_at')->dateTime()->since(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')->options([
                    'available' => 'Available',
                    'assigned' => 'Assigned',
                    'maintenance' => 'Maintenance',
                    'retired' => 'Retired',
                ]),
                \Filament\Tables\Filters\TernaryFilter::make('has_images')
                    ->label('Has Images')
                    ->queries(
                        true: fn ($query) => $query->has('images'),
                        false: fn ($query) => $query->doesntHave('images'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
