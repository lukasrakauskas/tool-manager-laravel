<?php

namespace App\Filament\Resources\Workers\Pages;

use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Workers\WorkerResource;

class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_tool')
                ->visible(fn (Worker $record): bool => $record->status === 'active')
                ->label('Assign Tool')
                ->modalHeading('Assign Tool')
                ->form([
                    Select::make('tool_id')
                        ->label('Tool')
                        ->options(fn (): array => Tool::query()->where('status', 'available')->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('due_at')
                        ->label('Due At (YYYY-MM-DD)')
                        ->placeholder('Optional')
                        ->rule('date')
                        ->nullable(),
                    TextInput::make('condition_out')->label('Condition Out')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, Worker $record): void {
                    $tool = Tool::query()->findOrFail($data['tool_id']);
                    $due = !empty($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null;
                    $actor = Auth::user();
                    Assignment::assign($tool, $record, $due, $data['condition_out'] ?? null, $actor instanceof User ? $actor : null);
                    $this->refreshFormData(['record']);
                    Notification::make()->title('Tool assigned')->success()->send();
                }),
            Action::make('return_tool')
                ->visible(fn (Worker $record): bool => $record->assignments()->where('status', 'assigned')->exists())
                ->label('Return Tool')
                ->modalHeading('Return Tool')
                ->form([
                    Select::make('tool_id')
                        ->label('Tool')
                        ->options(fn (Worker $record): array => Tool::query()
                            ->where('status', 'assigned')
                            ->whereHas('assignments', fn ($q) => $q->where('worker_id', $record->id)->where('status', 'assigned'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('condition_in')->label('Condition In')->maxLength(255)->nullable(),
                ])
                ->action(function (array $data, Worker $record): void {
                    $current = Assignment::query()
                        ->where('tool_id', $data['tool_id'])
                        ->where('worker_id', $record->id)
                        ->where('status', 'assigned')
                        ->latest('id')
                        ->first();

                    if ($current) {
                        $actor = Auth::user();
                        $current->markReturned($data['condition_in'] ?? null, $actor instanceof User ? $actor : null);
                        $this->refreshFormData(['record']);
                        Notification::make()->title('Tool returned')->success()->send();
                    }
                }),
            Action::make('transfer_tool')
                ->visible(fn (Worker $record): bool => $record->assignments()->where('status', 'assigned')->exists())
                ->label('Transfer Tool')
                ->modalHeading('Transfer Tool')
                ->form([
                    Select::make('tool_id')
                        ->label('Tool')
                        ->options(fn (Worker $record): array => Tool::query()
                            ->whereHas('assignments', fn ($q) => $q->where('worker_id', $record->id)->where('status', 'assigned'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('to_worker_id')
                        ->label('To Worker')
                        ->options(fn (Worker $record): array => Worker::query()
                            ->where('status', 'active')
                            ->where('id', '!=', $record->id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
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
                ->action(function (array $data, Worker $record): void {
                    $tool = Tool::query()->findOrFail($data['tool_id']);
                    $to = Worker::query()->findOrFail($data['to_worker_id']);
                    $due = ! empty($data['due_at']) ? new \DateTimeImmutable($data['due_at']) : null;
                    $actor = Auth::user();
                    Assignment::transfer($tool, $to, $due, $data['condition_in'] ?? null, $data['condition_out'] ?? null, $actor instanceof User ? $actor : null);
                    $this->refreshFormData(['record']);
                    Notification::make()->title('Tool transferred')->success()->send();
                }),
            Action::make('rotate_qr')
                ->label('Rotate QR')
                ->action(function (Worker $record): void {
                    $actor = Auth::user();
                    $record->rotateQrToken($actor instanceof User ? $actor : null);
                    Notification::make()->title('QR rotated')->success()->send();
                }),
            Action::make('show_qr')
                ->label('Show QR')
                ->modalHeading('Worker QR Code')
                ->modalContent(fn (Worker $record): string => Blade::render('<div class="p-4"><img src="'.e(route('qr.svg', ['type' => 'w', 'token' => $record->ensureActiveQrToken()->token])).'" alt="Worker QR" class="mx-auto"></div>')),
            DeleteAction::make(),
        ];
    }
}
