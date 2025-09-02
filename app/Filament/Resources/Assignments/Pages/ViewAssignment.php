<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Filament\Resources\Tools\ToolResource;
use App\Filament\Resources\Workers\WorkerResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Assignment')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('tool.name')
                                ->label('Tool')
                                ->url(fn ($record): string => ToolResource::getUrl('edit', ['record' => $record->tool_id]))
                                ->openUrlInNewTab(),
                            TextEntry::make('worker.name')
                                ->label('Worker')
                                ->url(fn ($record): string => WorkerResource::getUrl('view', ['record' => $record->worker_id]))
                                ->openUrlInNewTab(),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('assigned_at')->dateTime()->label('Assigned At'),
                            TextEntry::make('due_at')->dateTime()->label('Due At'),
                            TextEntry::make('returned_at')->dateTime()->label('Returned At'),
                            TextEntry::make('condition_out')->label('Condition Out'),
                            TextEntry::make('condition_in')->label('Condition In'),
                            TextEntry::make('updated_at')->dateTime()->since(),
                        ]),
                    ]),
            ]);
    }
}
