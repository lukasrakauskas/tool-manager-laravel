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
            DeleteAction::make(),
        ];
    }
}
