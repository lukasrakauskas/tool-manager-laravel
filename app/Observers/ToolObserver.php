<?php

namespace App\Observers;

use App\Models\Tool;
use Illuminate\Support\Facades\Auth;

class ToolObserver
{
    public function created(Tool $tool): void
    {
        $tool->auditLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'tool.created',
            'meta' => [
                'id' => $tool->id,
            ],
        ]);
    }

    public function updated(Tool $tool): void
    {
        $changed = array_values(array_filter(array_keys($tool->getChanges()), function (string $key): bool {
            return ! in_array($key, ['updated_at', 'qr_secret'], true);
        }));

        if (empty($changed)) {
            return;
        }

        $tool->auditLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'tool.updated',
            'meta' => [
                'changed' => $changed,
            ],
        ]);
    }

    public function deleted(Tool $tool): void
    {
        $tool->auditLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'tool.deleted',
            'meta' => [
                'id' => $tool->id,
            ],
        ]);
    }
}
