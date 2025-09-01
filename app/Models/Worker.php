<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\QrToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_code',
        'status',
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

    public function qrTokens(): MorphMany
    {
        return $this->morphMany(QrToken::class, 'subject');
    }

    public function ensureActiveQrToken(): QrToken
    {
        $current = $this->qrTokens()
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        if ($current) {
            return $current;
        }

        return $this->qrTokens()->create([
            'token' => bin2hex(random_bytes(16)),
        ]);
    }

    public function rotateQrToken(?User $actor = null): QrToken
    {
        $this->qrTokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);

        $qr = $this->qrTokens()->create([
            'token' => bin2hex(random_bytes(16)),
        ]);

        $this->auditLogs()->create([
            'user_id' => $actor?->id,
            'action' => 'qr.rotated',
            'meta' => [
                'subject' => 'worker',
            ],
            'created_at' => now(),
        ]);

        return $qr;
    }
}
