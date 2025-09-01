<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

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

    protected static function booted(): void
    {
        static::created(function (Tool $tool): void {
            $tool->auditLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'tool.created',
                'meta' => [
                    'id' => $tool->id,
                ],
            ]);
        });

        static::updated(function (Tool $tool): void {
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
        });

        static::deleted(function (Tool $tool): void {
            $tool->auditLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'tool.deleted',
                'meta' => [
                    'id' => $tool->id,
                ],
            ]);
        });
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
}
