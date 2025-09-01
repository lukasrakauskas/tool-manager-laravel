<?php

namespace App\Filament\Resources\Workers\RelationManagers;

use App\Filament\Resources\Tools\ToolResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'currentAssignments';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tool.name')
                    ->label('Tool')
                    ->url(fn ($record): string => ToolResource::getUrl('edit', ['record' => $record->tool_id]))
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('due_at')->dateTime()->label('Due'),
                TextColumn::make('assigned_at')->dateTime()->label('Assigned'),
            ])
            ->headerActions([])
            ->emptyStateHeading('No assigned tools');
    }
}
