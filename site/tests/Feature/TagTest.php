<?php

namespace Tests\Feature;

use App\Course;
use App\User;
use App\Tag;
use App\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->withoutExceptionHandling();
    }

    /**
     * A basic feature test example.
     */
    public function test_crud_tag(): void
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

        $this->actingAs($admin)
            ->post('/tags', $newTag)
            ->assertSessionHasErrors(['name']);

        // Update
        $tagId = Tag::where('name', $tagName)->first()->id;
        $updatedTag = array_merge($newTag, ['name' => fake()->word()]);

        $this->actingAs($admin)->put("/tags/$tagId", $updatedTag);
        $this->assertDatabaseMissing('tags', $newTag);
        $this->assertDatabaseHas('tags', $updatedTag);

        // Update with error
        $this->actingAs($admin)
            ->put("/tags/$tagId", ['name' => 'inv@l1de n@m3'])
            ->assertSessionHasErrors(['name']);

        // Delete
        $this->actingAs($admin)->delete("/tags/$tagId");
        $this->assertDatabaseMissing('tags', ['id' => $tagId]);
    }

    public function test_clone_tag(): void
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

    public function test_link_tag() : void
    {
        $admin = User::factory()->admin()->create();

        $course = Course::factory()->hasCards()->create();
        $card = $course->cards->first();

        // Create
        $response = $this->actingAs($admin)->put(
            "/cards/$card->id/createTag", ['name' => fake()->word()]
        );

        $tagId = $response['tag_id'];

        $this->assertDatabaseHas(
            'card_tag',
            ['tag_id' => $tagId, 'card_id' => $card->id],
        );

        // Create with error
        $response = $this->actingAs($admin)->put(
            "/cards/$card->id/createTag", ['name' => 'inv@l1de n@m3']
        );

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
}
