<?php

namespace App\Filament\Resources\Tools\Schemas;

use Filament\Schemas\Schema;

class ToolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('serial')->required()->unique(ignoreRecord: true),
                        \Filament\Forms\Components\Select::make('status')->options([
                            'available' => 'Available',
                            'assigned' => 'Assigned',
                            'maintenance' => 'Maintenance',
                            'retired' => 'Retired',
                        ])->required(),
                        \Filament\Forms\Components\TextInput::make('category')->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('power_watts')->numeric()->minValue(0),
                        \Filament\Forms\Components\TextInput::make('size')->maxLength(255),
                        \Filament\Forms\Components\KeyValue::make('attributes')->keyLabel('Key')->valueLabel('Value'),
                    ]),
            ]);
    }
}
