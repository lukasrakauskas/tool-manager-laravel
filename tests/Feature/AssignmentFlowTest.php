<?php

use App\Models\Assignment;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;

it('assigns and returns a tool', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $tool = Tool::factory()->create(['status' => 'available']);
    $worker = Worker::factory()->create(['status' => 'active']);

    $this->actingAs($user);

    $assignment = Assignment::assign($tool, $worker, Carbon::now()->addDay(), 'Good', $user);

    expect($assignment->exists)->toBeTrue();
    expect($assignment->status)->toBe('assigned');
    expect($assignment->tool->status)->toBe('assigned');

    $assignment->markReturned('Used', $user);

    expect($assignment->status)->toBe('returned');
    expect($assignment->tool->fresh()->status)->toBe('available');
});

it('transfers a tool between workers', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $tool = Tool::factory()->create(['status' => 'available']);
    $from = Worker::factory()->create(['status' => 'active']);
    $to = Worker::factory()->create(['status' => 'active']);

    $this->actingAs($user);

    $first = Assignment::assign($tool, $from, null, null, $user);

    [$returned, $new] = Assignment::transfer($tool->fresh(), $to, null, 'OK', 'Good', $user);

    expect($returned->status)->toBe('returned');
    expect($new->status)->toBe('assigned');
    expect($new->worker_id)->toBe($to->id);
    expect($tool->fresh()->status)->toBe('assigned');
});
