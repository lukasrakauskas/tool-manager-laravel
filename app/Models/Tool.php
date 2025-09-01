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
        'qr_secret',
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
}
