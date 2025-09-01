<?php

namespace App\Filament\Resources\Tools\Tables;

use App\Models\Tool;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;

class ToolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferFilters(false)
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('serial')->searchable()->sortable(),
                TextColumn::make('category')->toggleable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('power_watts')->numeric()->toggleable(),
                TextColumn::make('size')->toggleable(),
                TextColumn::make('updated_at')->dateTime()->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'available' => 'Available',
                    'assigned' => 'Assigned',
                    'maintenance' => 'Maintenance',
                    'retired' => 'Retired',
                ]),
                SelectFilter::make('brand')
                    ->label('Brand')
                    ->options(fn (): array => Tool::query()
                        ->selectRaw("distinct json_extract(attributes, '$.brand') as brand")
                        ->whereNotNull('attributes')
                        ->pluck('brand', 'brand')
                        ->filter()
                        ->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['value']) || $data['value'] === null || $data['value'] === '') {
                            return $query;
                        }

                        return $query->whereRaw("json_extract(attributes, '$.brand') = ?", [$data['value']]);
                    }),
                Filter::make('voltage')
                    ->label('Voltage')
                    ->schema([
                        TextInput::make('min')->numeric()->label('Min'),
                        TextInput::make('max')->numeric()->label('Max'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['min'] ?? null),
                                fn (Builder $q, $min): Builder => $q->whereRaw("CAST(json_extract(attributes, '$.voltage') AS INTEGER) >= ?", [$min]),
                            )
                            ->when(
                                filled($data['max'] ?? null),
                                fn (Builder $q, $max): Builder => $q->whereRaw("CAST(json_extract(attributes, '$.voltage') AS INTEGER) <= ?", [$max]),
                            );
                    }),
                TernaryFilter::make('has_images')
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
