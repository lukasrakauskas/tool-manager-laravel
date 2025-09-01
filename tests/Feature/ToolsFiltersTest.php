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
        ->filterTable('brand', 'Makita')
        ->assertCanSeeTableRecords(Tool::query()->whereRaw("json_extract(attributes, '$.brand') = ?", ['Makita'])->get())
        ->assertCanNotSeeTableRecords(Tool::query()->whereRaw("json_extract(attributes, '$.brand') = ?", ['Bosch'])->get());
});

it('filters tools by voltage range attribute', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $low = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 12]]);
    $mid = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 18]]);
    $high = Tool::factory()->create(['attributes' => ['brand' => 'Makita', 'voltage' => 24]]);

    $this->actingAs($user);

    Livewire::test(ListTools::class)
        ->filterTable('voltage', ['min' => 15, 'max' => 20, 'isActive' => true])
        ->assertCanSeeTableRecords([$mid])
        ->assertCanNotSeeTableRecords([$low, $high]);
});
