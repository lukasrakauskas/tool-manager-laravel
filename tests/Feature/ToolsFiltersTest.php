<?php

use App\Filament\Resources\Tools\Pages\ListTools;
use App\Models\Tool;
use App\Models\User;
use Livewire\Livewire;

it('filters tools by brand attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    Tool::factory()->count(2)->create(['attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    Tool::factory()->count(1)->create(['attributes' => ['brand' => 'Bosch', 'voltage' => 12]]);

    $this->actingAs($user);

    Livewire::test(ListTools::class)
        ->setTableFilter('brand', 'Makita')
        ->assertCountTableRecords(2);
});

it('filters tools by voltage range attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $low = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 12]]);
    $mid = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    $high = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 24]]);

    $this->actingAs($user);

    Livewire::test(ListTools::class)
        ->setTableFilter('voltage', ['min' => 15, 'max' => 20])
        ->assertCountTableRecords(1);
});
