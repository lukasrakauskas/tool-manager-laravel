<?php

use App\Models\Tool;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('throttle:qr')->group(function () {
    Route::get('/qr/{type}/{token}', function (Request $request, string $type, string $token) {
        abort_unless(in_array($type, ['t', 'w'], true), 404);

        if ($type === 't') {
            $tool = Tool::query()->where('qr_secret', $token)->first();
            if (! $tool) {
                abort(404);
            }

            return redirect()->to(route('filament.admin.resources.tools.edit', ['record' => $tool]));
        }

        $worker = Worker::query()->where('qr_secret', $token)->first();
        if (! $worker) {
            abort(404);
        }

        return redirect()->to(route('filament.admin.resources.workers.edit', ['record' => $worker]));
    })->name('qr.resolve');
});
