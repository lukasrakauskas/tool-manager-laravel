<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'serial',
        'status',
        'power_watts',
        'size',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => AsArrayObject::class,
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ToolImage::class);
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
                'subject' => 'tool',
            ],
            'created_at' => now(),
        ]);

        return $qr;
    }
}
