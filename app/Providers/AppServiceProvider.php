<?php

namespace App\Providers;

use App\Models\Tool;
use App\Models\Worker;
use App\Observers\ToolObserver;
use App\Observers\WorkerObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('qr', function ($request) {
            return [
                Limit::perMinute(60)->by($request->ip()),
            ];
        });

        Tool::observe(ToolObserver::class);
        Worker::observe(WorkerObserver::class);
    }
}
