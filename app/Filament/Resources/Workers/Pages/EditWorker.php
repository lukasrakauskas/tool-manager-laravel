<?php

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\Workers\WorkerResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_tool')
                ->visible(fn (\App\Models\Worker $record): bool => $record->status === 'active')
                ->label('Assign Tool')
                ->modalHeading('Assign Tool')
                ->form([
                    \Filament\Forms\Components\Select::make('tool_id')
                        ->label('Tool')
                        ->options(fn (): array => \App\Models\Tool::query()->where('status', 'available')->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('due_at')
                        ->label('Due At (YYYY-MM-DD)')
                        ->placeholder('Optional')
                        ->rule('date')
                        ->nullable(),
                    \Filament\Forms\Components\TextInput::make('condition_out')->label('Condition Out')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, \App\Models\Worker $record): void {
                    $tool = \App\Models\Tool::query()->findOrFail($data['tool_id']);
                    $due = ! empty($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null;
                    $actor = Auth::user();
                    \App\Models\Assignment::assign($tool, $record, $due, $data['condition_out'] ?? null, $actor instanceof \App\Models\User ? $actor : null);
                    $this->refreshFormData(['record']);
                    Notification::make()->title('Tool assigned')->success()->send();
                }),
            Action::make('return_tool')
                ->visible(fn (\App\Models\Worker $record): bool => $record->assignments()->where('status', 'assigned')->exists())
                ->label('Return Tool')
                ->modalHeading('Return Tool')
                ->form([
                    \Filament\Forms\Components\Select::make('tool_id')
                        ->label('Tool')
                        ->options(fn (\App\Models\Worker $record): array => \App\Models\Tool::query()
                            ->where('status', 'assigned')
                            ->whereHas('assignments', fn ($q) => $q->where('worker_id', $record->id)->where('status', 'assigned'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('condition_in')->label('Condition In')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, \App\Models\Worker $record): void {
                    $current = \App\Models\Assignment::query()
                        ->where('tool_id', $data['tool_id'])
                        ->where('worker_id', $record->id)
                        ->where('status', 'assigned')
                        ->latest('id')
                        ->first();

                    if ($current) {
                        $actor = Auth::user();
                        $current->markReturned($data['condition_in'] ?? null, $actor instanceof \App\Models\User ? $actor : null);
                        $this->refreshFormData(['record']);
                        Notification::make()->title('Tool returned')->success()->send();
                    }
                }),
            Action::make('transfer_tool')
                ->visible(fn (\App\Models\Worker $record): bool => $record->assignments()->where('status', 'assigned')->exists())
                ->label('Transfer Tool')
                ->modalHeading('Transfer Tool')
                ->form([
                    \Filament\Forms\Components\Select::make('tool_id')
                        ->label('Tool')
                        ->options(fn (\App\Models\Worker $record): array => \App\Models\Tool::query()
                            ->whereHas('assignments', fn ($q) => $q->where('worker_id', $record->id)->where('status', 'assigned'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    \Filament\Forms\Components\Select::make('to_worker_id')
                        ->label('To Worker')
                        ->options(fn (\App\Models\Worker $record): array => \App\Models\Worker::query()
                            ->where('status', 'active')
                            ->where('id', '!=', $record->id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('due_at')
                        ->label('New Due At (YYYY-MM-DD)')
                        ->placeholder('Optional')
                        ->rule('date')
                        ->nullable(),
                    \Filament\Forms\Components\TextInput::make('condition_in')->label('Condition In')->maxLength(255)->nullable(),
                    \Filament\Forms\Components\TextInput::make('condition_out')->label('Condition Out')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, \App\Models\Worker $record): void {
                    $tool = \App\Models\Tool::query()->findOrFail($data['tool_id']);
                    $to = \App\Models\Worker::query()->findOrFail($data['to_worker_id']);
                    $due = ! empty($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null;
                    $actor = Auth::user();
                    \App\Models\Assignment::transfer($tool, $to, $due, $data['condition_in'] ?? null, $data['condition_out'] ?? null, $actor instanceof \App\Models\User ? $actor : null);
                    $this->refreshFormData(['record']);
                    Notification::make()->title('Tool transferred')->success()->send();
                }),
            Action::make('rotate_qr')
                ->label('Rotate QR')
                ->action(function (\App\Models\Worker $record): void {
                    $actor = Auth::user();
                    $record->rotateQrToken($actor instanceof \App\Models\User ? $actor : null);
                    Notification::make()->title('QR rotated')->success()->send();
                }),
            Action::make('show_qr')
                ->label('Show QR')
                ->modalHeading('Worker QR Code')
                ->modalContent(fn (\App\Models\Worker $record): string => \Blade::render('<div class="p-4"><img src="'.e(route('qr.svg', ['type' => 'w', 'token' => $record->ensureActiveQrToken()->token])).'" alt="Worker QR" class="mx-auto"></div>')),
            DeleteAction::make(),
        ];
    }
}
