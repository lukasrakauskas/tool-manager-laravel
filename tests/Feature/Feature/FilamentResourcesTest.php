<?php

use function Pest\Laravel\get;

it('tools resource page loads', function () {
    $this->actingAs(\App\Models\User::factory()->create());
    get('/admin/tools')->assertOk()->assertSee('Tools');
});

it('workers resource page loads', function () {
    $this->actingAs(\App\Models\User::factory()->create());
    get('/admin/workers')->assertOk()->assertSee('Workers');
});
