<?php

namespace Tests\Feature\Livewire;

use App\Course;
use App\Livewire\Finder;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinderTest extends TestCase
{
    use RefreshDatabase;

    public function testInvalidQueryParams()
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        // Invalid query params are removed from filters.
        $emptyFilters = [
            'tag' => [],
            'editor' => [],
            'state' => [],
            'search' => [],
        ];

        $invalidFilters = array_merge($emptyFilters, ['state' => [100]]);

        Livewire::actingAs($admin)
            ->withQueryParams(['q' => $invalidFilters])
            ->test(Finder::class, ['course' => $course])
            ->assertSet('arrayFilters', $emptyFilters);
    }

    public function testValidQueryParams()
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $stateId = $course->states()->first()->id;

        $filters = [
            'tag' => [],
            'editor' => [],
            'state' => [$stateId],
            'search' => [],
        ];

        Livewire::actingAs($admin)
            ->withQueryParams(['q' => $filters])
            ->test(Finder::class, ['course' => $course])
            ->assertSet('arrayFilters', $filters);
    }

    public function testAddInvalidFilter()
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $emptyFilters = [
            'tag' => [],
            'editor' => [],
            'state' => [],
            'search' => [],
        ];

        Livewire::actingAs($admin)
            ->test(Finder::class, ['course' => $course])
            ->dispatch('add-element-to-filter', filter: 100, type: 'state')
            ->assertSet('arrayFilters', $emptyFilters);
    }

    public function testAddValidFilter()
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $stateId = $course->states()->first()->id;

        $emptyFilters = [
            'tag' => [],
            'editor' => [],
            'state' => [$stateId],
            'search' => [],
        ];

        Livewire::actingAs($admin)
            ->test(Finder::class, ['course' => $course])
            ->dispatch('add-element-to-filter', filter: $stateId, type: 'state')
            ->assertSet('arrayFilters', $emptyFilters);
    }
}
