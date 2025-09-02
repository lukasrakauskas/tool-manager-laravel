<?php

namespace App\Filament\Resources\Assignments\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tool.name')->label('Tool')->searchable()->sortable(),
                TextColumn::make('worker.name')->label('Worker')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('assigned_at')->dateTime()->since()->label('Assigned'),
                TextColumn::make('due_at')->dateTime()->label('Due'),
                TextColumn::make('returned_at')->dateTime()->label('Returned')->toggleable(),
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
