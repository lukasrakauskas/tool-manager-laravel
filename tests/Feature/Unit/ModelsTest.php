<?php

it('can create tool, worker, and assignment', function () {
    $tool = \App\Models\Tool::factory()->create();
    $worker = \App\Models\Worker::factory()->create();
    $assignment = \App\Models\Assignment::factory()->create([
        'tool_id' => $tool->id,
        'worker_id' => $worker->id,
    ]);

    expect($assignment->tool->id)->toBe($tool->id);
    expect($assignment->worker->id)->toBe($worker->id);
});
