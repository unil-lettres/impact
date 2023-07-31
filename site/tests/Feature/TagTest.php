<?php

namespace Tests\Feature;

use App\Course;
use App\Tag;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic feature test example.
     */
    public function testCrudTag(): void
    {
        $admin = User::factory()->admin()->create();

        $course = Course::factory()->create();
        $tagName = fake()->word();

        // Create
        $newTag = ['course_id' => $course->id, 'name' => $tagName];

        $this->actingAs($admin)->post('/tags', $newTag);
        $this->assertDatabaseHas('tags', $newTag);

        // Create with error
        $newTag = ['course_id' => $course->id, 'name' => 'inv@l1de n@m3'];

        $this->withExceptionHandling()
            ->actingAs($admin)
            ->post('/tags', $newTag)
            ->assertSessionHasErrors(['name']);

        // Update
        $tagId = Tag::where('name', $tagName)->first()->id;
        $updatedTag = array_merge($newTag, ['name' => fake()->word()]);

        $this->actingAs($admin)->put("/tags/$tagId", $updatedTag);
        $this->assertDatabaseMissing('tags', $newTag);
        $this->assertDatabaseHas('tags', $updatedTag);

        // Update with error
        $this->withExceptionHandling()
            ->actingAs($admin)
            ->put("/tags/$tagId", ['name' => 'inv@l1de n@m3'])
            ->assertSessionHasErrors(['name']);

        // Delete
        $this->actingAs($admin)->delete("/tags/$tagId");
        $this->assertDatabaseMissing('tags', ['id' => $tagId]);
    }

    public function testCloneTag(): void
    {
        $admin = User::factory()->admin()->create();

        $courseFrom = Course::factory()->hasTags(3)->create();
        $courseTo = Course::factory()->create();

        $this->actingAs($admin)->post(
            "/courses/$courseTo->id/cloneTags", ['course_id' => $courseFrom->id]
        );

        $courseFrom->tags->each(
            fn ($tagFrom) => $this->assertDatabaseHas(
                'tags', ['name' => $tagFrom->name, 'course_id' => $courseTo->id]
            )
        );
    }

    public function testLinkTag(): void
    {
        $admin = User::factory()->admin()->create();

        $course = Course::factory()->hasCards()->create();
        $card = $course->cards->first();

        // Create
        $response = $this->actingAs($admin)->put(
            "/cards/$card->id/createTag",
            ['name' => fake()->word(), 'course_id' => $course->id]
        );

        $tagId = $response['tag_id'];

        $this->assertDatabaseHas(
            'card_tag',
            ['tag_id' => $tagId, 'card_id' => $card->id],
        );

        // Create with error
        $this->withExceptionHandling()->actingAs($admin)->put(
            "/cards/$card->id/createTag",
            ['name' => 'inv@l1de n@m3', 'course_id' => $course->id]
        )->assertSessionHasErrors(['name']);

        // Unlink
        $response = $this->actingAs($admin)->put(
            "/cards/$card->id/unlink/$tagId",
        );

        $this->assertDatabaseMissing(
            'card_tag',
            ['tag_id' => $tagId, 'card_id' => $card->id],
        );

        // Link
        $response = $this->actingAs($admin)->put(
            "/cards/$card->id/link/$tagId",
        );

        $this->assertDatabaseHas(
            'card_tag',
            ['tag_id' => $tagId, 'card_id' => $card->id],
        );
    }

    public function testOrderTags()
    {
        $admin = User::factory()->admin()->create();

        $course = Course::factory()->hasTags(
            3,
            // We need to be sure that tag names are not interferring with other
            // words in the page.
            ['name' => fn () => fake()->uuid()],
        )->hasCards(5)->create();

        // Here we link some tags to the cards, so we can test the order.
        $course->tags->first()->cards()->attach(
            $course->cards->pluck('id')->toArray(),
        );
        // We use slice to link more or less tags to the cards to order by
        // cards_count afterward.
        $course->tags->get(1)->cards()->attach(
            $course->cards->slice(1)->pluck('id')->toArray(),
        );
        $course->tags->last()->cards()->attach(
            $course->cards->slice(3)->pluck('id')->toArray(),
        );

        $response = $this->actingAs($admin)->get(
            "/courses/$course->id/configure/tags?tag_order=cards_count&tag_direction=asc",
        );
        $response->assertSeeInOrder($course->tags->sortBy(
            fn ($tag) => $tag->cards->count()
        )->pluck('name')->toArray());
    }
}
