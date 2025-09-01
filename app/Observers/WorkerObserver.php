<?php

namespace App\Observers;

use App\Models\Worker;
use Illuminate\Support\Facades\Auth;

class WorkerObserver
{
    public function created(Worker $worker): void
    {
        $worker->auditLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'worker.created',
            'meta' => [
                'id' => $worker->id,
            ],
        ]);
    }

    public function updated(Worker $worker): void
    {
        $changed = array_values(array_filter(array_keys($worker->getChanges()), function (string $key): bool {
            return ! in_array($key, ['updated_at', 'qr_secret'], true);
        }));

        if (empty($changed)) {
            return;
        }

        $worker->auditLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'worker.updated',
            'meta' => [
                'changed' => $changed,
            ],
        ]);
    }

    public function deleted(Worker $worker): void
    {
        $worker->auditLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'worker.deleted',
            'meta' => [
                'id' => $worker->id,
            ],
        ]);
    }
}
