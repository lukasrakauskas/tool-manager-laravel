<?php

namespace App\Filament\Resources\Workers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class WorkerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('external_code')->maxLength(255),
                        Select::make('status')->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])->required(),
                    ]),
            ]);
    }
}
