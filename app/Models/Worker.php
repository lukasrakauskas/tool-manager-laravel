<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_code',
        'status',
        'qr_secret',
    ];

    protected static function booted(): void
    {
        static::created(function (Worker $worker): void {
            $worker->auditLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'worker.created',
                'meta' => [
                    'id' => $worker->id,
                ],
            ]);
        });

        static::updated(function (Worker $worker): void {
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
        });

        static::deleted(function (Worker $worker): void {
            $worker->auditLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'worker.deleted',
                'meta' => [
                    'id' => $worker->id,
                ],
            ]);
        });
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function currentAssignments(): HasMany
    {
        return $this->assignments()->whereNull('returned_at');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
