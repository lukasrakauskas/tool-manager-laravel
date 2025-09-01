<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'worker_id',
        'assigned_at',
        'due_at',
        'returned_at',
        'status',
        'condition_out',
        'condition_in',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    public static function assign(Tool $tool, Worker $worker, ?\DateTimeInterface $dueAt = null, ?string $conditionOut = null, ?User $actor = null): self
    {
        if ($tool->status !== 'available') {
            throw ValidationException::withMessages(['tool_id' => 'Tool is not available.']);
        }

        if ($worker->status !== 'active') {
            throw ValidationException::withMessages(['worker_id' => 'Worker is not active.']);
        }

        return DB::transaction(function () use ($tool, $worker, $dueAt, $conditionOut, $actor): self {
            $freshTool = Tool::query()->whereKey($tool->id)->lockForUpdate()->firstOrFail();

            $open = self::query()
                ->where('tool_id', $freshTool->id)
                ->where('status', 'assigned')
                ->exists();

            if ($open) {
                throw ValidationException::withMessages(['tool_id' => 'Tool already assigned.']);
            }

            $assignment = self::query()->create([
                'tool_id' => $freshTool->id,
                'worker_id' => $worker->id,
                'assigned_at' => Carbon::now(),
                'due_at' => $dueAt,
                'status' => 'assigned',
                'condition_out' => $conditionOut,
            ]);

            $freshTool->status = 'assigned';
            $freshTool->save();

            $assignment->auditLogs()->create([
                'user_id' => $actor?->id,
                'action' => 'assignment.created',
                'meta' => [
                    'tool_id' => $freshTool->id,
                    'worker_id' => $worker->id,
                ],
                'created_at' => Carbon::now(),
            ]);

            return $assignment;
        });
    }

    public function markReturned(?string $conditionIn = null, ?User $actor = null): self
    {
        if ($this->status !== 'assigned' || $this->returned_at !== null) {
            throw ValidationException::withMessages(['returned_at' => 'Assignment already returned.']);
        }

        return DB::transaction(function (): self {
            $this->returned_at = Carbon::now();
            $this->status = 'returned';
            $this->condition_in = $this->condition_in ?: null;
            if ($this->condition_in === null) {
                $this->condition_in = $conditionIn ?? null;
            }
            $this->save();

            $tool = Tool::query()->whereKey($this->tool_id)->lockForUpdate()->firstOrFail();
            $tool->status = 'available';
            $tool->save();

            $this->auditLogs()->create([
                'user_id' => $actor?->id,
                'action' => 'assignment.returned',
                'meta' => [
                    'tool_id' => $tool->id,
                    'worker_id' => $this->worker_id,
                ],
                'created_at' => Carbon::now(),
            ]);

            return $this;
        });
    }

    public static function transfer(Tool $tool, Worker $to, ?\DateTimeInterface $dueAt = null, ?string $conditionIn = null, ?string $conditionOut = null, ?User $actor = null): array
    {
        if ($to->status !== 'active') {
            throw ValidationException::withMessages(['worker_id' => 'Worker is not active.']);
        }

        return DB::transaction(function () use ($tool, $to, $dueAt, $conditionIn, $conditionOut, $actor): array {
            $current = self::query()
                ->where('tool_id', $tool->id)
                ->where('status', 'assigned')
                ->latest('id')
                ->first();

            if (! $current) {
                throw ValidationException::withMessages(['tool_id' => 'Tool is not currently assigned.']);
            }

            $current->markReturned($conditionIn, $actor);

            $new = self::assign($tool->fresh(), $to, $dueAt, $conditionOut, $actor);

            return [$current, $new];
        });
    }
}
