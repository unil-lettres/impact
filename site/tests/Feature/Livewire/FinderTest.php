<?php

namespace Tests\Feature\Livewire;

use App\Card;
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

    public function test_handle_sort_reorders_the_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        $cards = collect(range(0, 2))->map(
            fn ($position) => Card::factory()->create([
                'course_id' => $course->id,
                'position' => $position,
            ])
        );

        Livewire::actingAs($admin)
            ->test(Finder::class, ['course' => $course])
            ->call('handleSort', 'card-'.$cards->first()->id, 2);

        // The first card moved to the last position, the others moved up.
        $this->assertSame(2, $cards->first()->fresh()->position);
        $this->assertSame(0, $cards->get(1)->fresh()->position);
        $this->assertSame(1, $cards->get(2)->fresh()->position);
    }

    public function test_handle_sort_ignores_an_item_that_is_not_listed(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $card = Card::factory()->create(['course_id' => $course->id, 'position' => 0]);

        // A card of another course is not part of the listing.
        $other = Card::factory()->create(['position' => 5]);

        Livewire::actingAs($admin)
            ->test(Finder::class, ['course' => $course])
            ->call('handleSort', 'card-'.$other->id, 0);

        $this->assertSame(5, $other->fresh()->position);
        $this->assertSame(0, $card->fresh()->position);
    }
}
