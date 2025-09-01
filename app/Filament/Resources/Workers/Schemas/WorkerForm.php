<?php

namespace App\Filament\Resources\Workers\Schemas;

use Filament\Schemas\Schema;

class WorkerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('external_code')->maxLength(255),
                        \Filament\Forms\Components\Select::make('status')->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])->required(),
                    ]),
            ]);
    }
}
