<?php

namespace App\Filament\Resources\Workers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class WorkersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('external_code')->searchable()->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('updated_at')->dateTime()->since(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
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
