<?php

namespace App\Filament\Resources\Tools\Pages;

use App\Filament\Resources\Tools\ToolResource;
use App\Models\Assignment;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign')
                ->visible(fn (Tool $record): bool => $record->status === 'available')
                ->label('Assign')
                ->modalHeading('Assign Tool')
                ->form([
                    Select::make('worker_id')
                        ->label('Worker')
                        ->relationship('worker', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('due_at')
                        ->label('Due At (YYYY-MM-DD)')
                        ->placeholder('Optional')
                        ->rule('date')
                        ->nullable(),
                    TextInput::make('condition_out')->label('Condition Out')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, Tool $record): void {
                    $worker = Worker::query()->findOrFail($data['worker_id']);
                    $due = ! empty($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null;
                    $actor = Auth::user();
                    Assignment::assign($record, $worker, $due, $data['condition_out'] ?? null, $actor instanceof User ? $actor : null);
                    $this->refreshFormData(['record']);
                    $this->notify('success', 'Tool assigned');
                }),
            Action::make('return')
                ->visible(fn (Tool $record): bool => $record->status === 'assigned')
                ->label('Return')
                ->modalHeading('Return Tool')
                ->form([
                    TextInput::make('condition_in')->label('Condition In')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, Tool $record): void {
                    $current = Assignment::query()
                        ->where('tool_id', $record->id)
                        ->where('status', 'assigned')
                        ->latest('id')
                        ->first();

                    if ($current) {
                        $actor = Auth::user();
                        $current->markReturned($data['condition_in'] ?? null, $actor instanceof User ? $actor : null);
                        $this->refreshFormData(['record']);
                        $this->notify('success', 'Tool returned');
                    }
                }),
            Action::make('transfer')
                ->visible(fn (Tool $record): bool => $record->status === 'assigned')
                ->label('Transfer')
                ->modalHeading('Transfer Tool')
                ->form([
                    Select::make('to_worker_id')
                        ->label('To Worker')
                        ->relationship('worker', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('due_at')
                        ->label('New Due At (YYYY-MM-DD)')
                        ->placeholder('Optional')
                        ->rule('date')
                        ->nullable(),
                    TextInput::make('condition_in')->label('Condition In')->maxLength(255)->nullable(),
                    TextInput::make('condition_out')->label('Condition Out')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, Tool $record): void {
                    $to = Worker::query()->findOrFail($data['to_worker_id']);
                    $due = ! empty($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null;
                    $actor = Auth::user();
                    Assignment::transfer($record, $to, $due, $data['condition_in'] ?? null, $data['condition_out'] ?? null, $actor instanceof User ? $actor : null);
                    $this->refreshFormData(['record']);
                    $this->notify('success', 'Tool transferred');
                }),
            DeleteAction::make(),
        ];
    }
}
