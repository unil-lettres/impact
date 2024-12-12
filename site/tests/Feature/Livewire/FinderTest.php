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

    public function test_invalid_query_params(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        // Invalid query params are removed from filters.
        $emptyFilters = [
            'tag' => [],
            'holder' => [],
            'state' => [],
            'search' => [],
        ];

        $invalidFilters = array_merge($emptyFilters, ['state' => [100]]);

        Livewire::actingAs($admin)
            ->withQueryParams(['q' => $invalidFilters])
            ->test(Finder::class, ['course' => $course])
            ->assertSet('arrayFilters', $emptyFilters);
    }

    public function test_valid_query_params(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $stateId = $course->states()->first()->id;

        $filters = [
            'tag' => [],
            'holder' => [],
            'state' => [$stateId],
            'search' => [],
        ];

        Livewire::actingAs($admin)
            ->withQueryParams(['q' => $filters])
            ->test(Finder::class, ['course' => $course])
            ->assertSet('arrayFilters', $filters);
    }

    public function test_add_invalid_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $emptyFilters = [
            'tag' => [],
            'holder' => [],
            'state' => [],
            'search' => [],
        ];

        Livewire::actingAs($admin)
            ->test(Finder::class, ['course' => $course])
            ->dispatch('add-element-to-filter', filter: 100, type: 'state')
            ->assertSet('arrayFilters', $emptyFilters);
    }

    public function test_add_valid_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $stateId = $course->states()->first()->id;

        $emptyFilters = [
            'tag' => [],
            'holder' => [],
            'state' => [$stateId],
            'search' => [],
        ];

        Livewire::actingAs($admin)
            ->test(Finder::class, ['course' => $course])
            ->dispatch('add-element-to-filter', filter: $stateId, type: 'state')
            ->assertSet('arrayFilters', $emptyFilters);
    }
}
