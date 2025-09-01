<?php

use App\Models\QrToken;
use App\Models\Tool;
use App\Models\User;
use App\Models\Worker;

use function Pest\Laravel\get;

it('serves QR SVG for tool', function () {
    $this->actingAs(User::factory()->create());

    $tool = Tool::factory()->create(['status' => 'available']);
    $qr = QrToken::create([
        'subject_type' => Tool::class,
        'subject_id' => $tool->id,
        'token' => bin2hex(random_bytes(16)),
    ]);

    $res = get("/qr/svg/t/{$qr->token}");
    $res->assertOk();
    expect($res->headers->get('content-type'))->toStartWith('image/svg+xml');
    expect($res->getContent())->toContain('<svg');
});

it('serves QR SVG for worker', function () {
    $this->actingAs(User::factory()->create());

    $worker = Worker::factory()->create();
    $qr = QrToken::create([
        'subject_type' => Worker::class,
        'subject_id' => $worker->id,
        'token' => bin2hex(random_bytes(16)),
    ]);

    $res = get("/qr/svg/w/{$qr->token}");
    $res->assertOk();
    expect($res->headers->get('content-type'))->toStartWith('image/svg+xml');
    expect($res->getContent())->toContain('<svg');
});

it('rejects invalid or revoked/expired tokens for svg', function () {
    $this->actingAs(User::factory()->create());

    $tool = Tool::factory()->create();
    $revoked = QrToken::create([
        'subject_type' => Tool::class,
        'subject_id' => $tool->id,
        'token' => bin2hex(random_bytes(16)),
        'revoked_at' => now(),
    ]);

    get("/qr/svg/t/{$revoked->token}")->assertNotFound();

    $expired = QrToken::create([
        'subject_type' => Tool::class,
        'subject_id' => $tool->id,
        'token' => bin2hex(random_bytes(16)),
        'expires_at' => now()->subMinute(),
    ]);

    get("/qr/svg/t/{$expired->token}")->assertNotFound();
});
