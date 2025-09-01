<?php

namespace App\Filament\Resources\Tools\Pages;

use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Tools\ToolResource;
use Illuminate\Support\Facades\Auth as SupportAuth;

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
                ->schema([
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
                    $actor = SupportAuth::user();
                    Assignment::assign($record, $worker, $due, $data['condition_out'] ?? null, $actor instanceof User ? $actor : null);
                    $this->refreshFormData(['record']);
                    Notification::make()->title('Tool assigned')->success()->send();
                }),
            Action::make('return')
                ->visible(fn (Tool $record): bool => $record->status === 'assigned')
                ->label('Return')
                ->modalHeading('Return Tool')
                ->schema([
                    TextInput::make('condition_in')->label('Condition In')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, Tool $record): void {
                    $current = Assignment::query()
                        ->where('tool_id', $record->id)
                        ->where('status', 'assigned')
                        ->latest('id')
                        ->first();

                    if ($current) {
                        $actor = SupportAuth::user();
                        $current->markReturned($data['condition_in'] ?? null, $actor instanceof User ? $actor : null);
                        $this->refreshFormData(['record']);
                        Notification::make()->title('Tool returned')->success()->send();
                    }
                }),
            Action::make('transfer')
                ->visible(fn (Tool $record): bool => $record->status === 'assigned')
                ->label('Transfer')
                ->modalHeading('Transfer Tool')
                ->schema([
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
                    $actor = SupportAuth::user();
                    Assignment::transfer($record, $to, $due, $data['condition_in'] ?? null, $data['condition_out'] ?? null, $actor instanceof User ? $actor : null);
                    $this->refreshFormData(['record']);
                    Notification::make()->title('Tool transferred')->success()->send();
                }),
            Action::make('rotate_qr')
                ->label('Rotate QR')
                ->action(function (Tool $record): void {
                    $actor = SupportAuth::user();
                    $record->rotateQrToken($actor instanceof User ? $actor : null);
                    Notification::make()->title('QR rotated')->success()->send();
                }),
            Action::make('show_qr')
                ->label('Show QR')
                ->modalHeading('Tool QR Code')
                ->modalContent(fn (Tool $record): string => Blade::render('<div class="p-4"><img src="'.e(route('qr.svg', ['type' => 't', 'token' => $record->ensureActiveQrToken()->token])).'" alt="Tool QR" class="mx-auto"></div>')),
            DeleteAction::make(),
        ];
    }
}
