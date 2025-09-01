<?php

use App\Models\Tool;
use App\Models\Worker;
use App\Models\QrToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('throttle:qr')->group(function () {
    Route::get('/qr/{type}/{token}', function (Request $request, string $type, string $token) {
        abort_unless(in_array($type, ['t', 'w'], true), 404);

        $qr = QrToken::query()
            ->where('token', $token)
            ->whereNull('revoked_at')
            ->when(true, fn ($q) => $q->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            }))
            ->first();

        if (! $qr) {
            abort(404);
        }

        if ($type === 't') {
            abort_unless($qr->subject_type === Tool::class, 404);
            $tool = Tool::query()->find($qr->subject_id);
            abort_if(! $tool, 404);

            return redirect()->to(route('filament.admin.resources.tools.edit', ['record' => $tool]));
        }

        abort_unless($qr->subject_type === Worker::class, 404);
        $worker = Worker::query()->find($qr->subject_id);
        abort_if(! $worker, 404);

        return redirect()->to(route('filament.admin.resources.workers.edit', ['record' => $worker]));
    })->name('qr.resolve');

    Route::get('/qr/svg/{type}/{token}', function (Request $request, string $type, string $token) {
        abort_unless(in_array($type, ['t', 'w'], true), 404);

        $qr = QrToken::query()
            ->where('token', $token)
            ->whereNull('revoked_at')
            ->when(true, fn ($q) => $q->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            }))
            ->first();

        if (! $qr) {
            abort(404);
        }

        if ($type === 't') {
            abort_unless($qr->subject_type === Tool::class, 404);
        } else {
            abort_unless($qr->subject_type === Worker::class, 404);
        }

        $url = route('qr.resolve', ['type' => $type, 'token' => $token]);
        $svg = QrCode::format('svg')->size(256)->margin(1)->generate($url);

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    })->name('qr.svg');
});
