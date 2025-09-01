<?php

use App\Filament\Resources\Tools\Pages\ListTools;
use App\Models\Tool;
use App\Models\User;
use function Pest\Livewire\livewire;

it('filters tools by brand attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    Tool::factory()->count(2)->create(['attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    Tool::factory()->count(1)->create(['attributes' => ['brand' => 'Bosch', 'voltage' => 12]]);

    livewire(ListTools::class)
        ->actingAs($user)
        ->filterTable('brand', 'Makita')
        ->assertCanSeeTableRecords(Tool::query()->whereRaw("json_extract(attributes, '$.brand') = ?", ['Makita'])->get())
        ->assertCanNotSeeTableRecords(Tool::query()->whereRaw("json_extract(attributes, '$.brand') = ?", ['Bosch'])->get());
});

it('filters tools by voltage range attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $low = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 12]]);
    $mid = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    $high = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 24]]);

    livewire(ListTools::class)
        ->actingAs($user)
        ->filterTable('voltage', ['min' => 15, 'max' => 20])
        ->assertCanSeeTableRecords([$mid])
        ->assertCanNotSeeTableRecords([$low, $high]);
});
