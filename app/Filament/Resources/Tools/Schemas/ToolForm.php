<?php

namespace App\Filament\Resources\Tools\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ToolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('serial')->required()->unique(ignoreRecord: true),
                        Select::make('status')->options([
                            'available' => 'Available',
                            'assigned' => 'Assigned',
                            'maintenance' => 'Maintenance',
                            'retired' => 'Retired',
                        ])->required(),
                        TextInput::make('category')->maxLength(255),
                        TextInput::make('power_watts')->numeric()->minValue(0),
                        TextInput::make('size')->maxLength(255),
                        KeyValue::make('attributes')->keyLabel('Key')->valueLabel('Value'),
                    ]),
            ]);
    }
}
