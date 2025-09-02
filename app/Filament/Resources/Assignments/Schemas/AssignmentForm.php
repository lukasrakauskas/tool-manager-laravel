<?php

namespace App\Filament\Resources\Assignments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('tool_id')
                            ->label('Tool')
                            ->relationship('tool', 'name')
                            ->required(),
                        Select::make('worker_id')
                            ->label('Worker')
                            ->relationship('worker', 'name')
                            ->required(),
                        DateTimePicker::make('assigned_at')->label('Assigned At')->required(),
                        DateTimePicker::make('due_at')->label('Due At'),
                        DateTimePicker::make('returned_at')->label('Returned At'),
                        Select::make('status')->options([
                            'assigned' => 'Assigned',
                            'returned' => 'Returned',
                        ])->required(),
                        TextInput::make('condition_out')->maxLength(255),
                        TextInput::make('condition_in')->maxLength(255),
                    ]),
            ]);
    }
}
