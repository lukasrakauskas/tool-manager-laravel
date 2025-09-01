<?php

use App\Models\QrToken;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;

use function Pest\Laravel\get;

it('resolves tool QR token', function () {
    $this->actingAs(User::factory()->create());

    $tool = Tool::factory()->create(['status' => 'available']);
    $qr = QrToken::create([
        'subject_type' => Tool::class,
        'subject_id' => $tool->id,
        'token' => bin2hex(random_bytes(16)),
    ]);

    get("/qr/t/{$qr->token}")->assertRedirect(route('filament.admin.resources.tools.edit', ['record' => $tool]));
});

it('rejects revoked or expired tokens', function () {
    $this->actingAs(User::factory()->create());

    $tool = Tool::factory()->create();
    $revoked = QrToken::create([
        'subject_type' => Tool::class,
        'subject_id' => $tool->id,
        'token' => bin2hex(random_bytes(16)),
        'revoked_at' => now(),
    ]);

    get("/qr/t/{$revoked->token}")->assertNotFound();

    $expired = QrToken::create([
        'subject_type' => Tool::class,
        'subject_id' => $tool->id,
        'token' => bin2hex(random_bytes(16)),
        'expires_at' => now()->subMinute(),
    ]);

    get("/qr/t/{$expired->token}")->assertNotFound();
});

it('resolves worker QR token', function () {
    $this->actingAs(User::factory()->create());

    $worker = Worker::factory()->create();
    $qr = QrToken::create([
        'subject_type' => Worker::class,
        'subject_id' => $worker->id,
        'token' => bin2hex(random_bytes(16)),
    ]);

    get("/qr/w/{$qr->token}")->assertRedirect(route('filament.admin.resources.workers.edit', ['record' => $worker]));
});
