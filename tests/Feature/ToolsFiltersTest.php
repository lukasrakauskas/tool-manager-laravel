<?php

use App\Models\Tool;
use App\Models\User;
use function Pest\Laravel\get;

it('filters tools by brand attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $m1 = Tool::factory()->create(['name' => 'Makita One', 'attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    $m2 = Tool::factory()->create(['name' => 'Makita Two', 'attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    $b1 = Tool::factory()->create(['name' => 'Bosch One', 'attributes' => ['brand' => 'Bosch', 'voltage' => 12]]);

    $this->actingAs($user);

    get('/admin/tools?filters[brand][value]=Makita')
        ->assertOk()
        ->assertSee('Makita One')
        ->assertSee('Makita Two')
        ->assertDontSee('Bosch One');
});

it('filters tools by voltage range attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $low = Tool::factory()->create(['name' => 'Low V', 'attributes' => ['brand' => 'Makita', 'voltage' => 12]]);
    $mid = Tool::factory()->create(['name' => 'Mid V', 'attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    $high = Tool::factory()->create(['name' => 'High V', 'attributes' => ['brand' => 'Makita', 'voltage' => 24]]);

    $this->actingAs($user);

    get('/admin/tools?filters[voltage][min]=15&filters[voltage][max]=20&filters[voltage][isActive]=1')
        ->assertOk()
        ->assertSee('Mid V')
        ->assertDontSee('Low V')
        ->assertDontSee('High V');
});
