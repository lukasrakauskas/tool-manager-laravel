<?php

use App\Models\Assignment;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Validation\ValidationException;

it('prevents assigning a non-available tool', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $tool = Tool::factory()->create(['status' => 'assigned']);
    $worker = Worker::factory()->create(['status' => 'active']);

    $this->actingAs($user);

    expect(fn () => Assignment::assign($tool, $worker, null, null, $user))
        ->toThrow(ValidationException::class);
});

it('prevents assigning to inactive worker', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $tool = Tool::factory()->create(['status' => 'available']);
    $worker = Worker::factory()->create(['status' => 'inactive']);

    $this->actingAs($user);

    expect(fn () => Assignment::assign($tool, $worker, null, null, $user))
        ->toThrow(ValidationException::class);
});

it('prevents double assignment for same tool', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $tool = Tool::factory()->create(['status' => 'available']);
    $w1 = Worker::factory()->create(['status' => 'active']);
    $w2 = Worker::factory()->create(['status' => 'active']);

    $this->actingAs($user);

    Assignment::assign($tool, $w1, null, null, $user);

    expect(fn () => Assignment::assign($tool->fresh(), $w2, null, null, $user))
        ->toThrow(ValidationException::class);
});
