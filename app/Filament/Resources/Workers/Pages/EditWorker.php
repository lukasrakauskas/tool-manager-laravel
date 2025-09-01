<?php

namespace App\Filament\Resources\Workers\Pages;

use App\Filament\Resources\Workers\WorkerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;

class EditWorker extends EditRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rotate_qr')
                ->label('Rotate QR')
                ->action(function (\App\Models\Worker $record): void {
                    $actor = Auth::user();
                    $record->rotateQrToken($actor instanceof \App\Models\User ? $actor : null);
                    $this->notify('success', 'QR rotated');
                }),
            Action::make('show_qr')
                ->label('Show QR')
                ->modalHeading('Worker QR Code')
                ->modalContent(fn (\App\Models\Worker $record): string => \Blade::render('<div class="p-4"><img src="'.e(route('qr.svg', ['type' => 'w', 'token' => $record->ensureActiveQrToken()->token])).'" alt="Worker QR" class="mx-auto"></div>')),
            DeleteAction::make(),
        ];
    }
}
