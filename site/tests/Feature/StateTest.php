<?php

namespace Tests\Feature;

use App\Card;
use App\Course;
use App\Enums\StateType;
use App\Mail\StateSelected;
use App\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_state_can_be_created(): void
    {
        $stateData = [
            'name' => fake()->word(),
        ];

        $state = State::factory()->create($stateData);

        $this->assertInstanceOf(State::class, $state);
        $this->assertDatabaseHas('states', $stateData);
    }

    public function test_course_default_states_should_be_automatically_created(): void
    {
        $course = Course::factory()->create();

        // When creating a course, 4 default states should be automatically
        // created and related to the course
        $this->assertEquals(4, $course->states->count());
    }

    public function test_card_state_should_be_automatically_setted_to_private(): void
    {
        $card = Card::factory()->create();

        $default_state = $card
            ->course
            ->states->where(
                'type', StateType::Private
            )->first();

        // When creating a card, the state should be automatically
        // setted to the course's private state
        $this->assertEquals($default_state->id, $card->state_id);
    }

    public function test_state_can_be_updated(): void
    {
        $state = State::factory()->create();

        $stateDataUpdated = [
            'name' => 'Updated Test State Name',
        ];

        $state->update($stateDataUpdated);

        $this->assertDatabaseHas('states', $stateDataUpdated);
    }

    public function test_state_can_be_deleted(): void
    {
        $state = State::factory()->create();

        $state->delete();

        $this->assertSoftDeleted($state);
    }

    /**
     * Test the state selected email content.
     */
    public function test_state_selected_email_content(): void
    {
        $card = Card::factory()
            ->create();

        $subject = $this->faker->sentence(3);
        $content = '{{title}} {{url}}';

        $mailable = new StateSelected(
            $card,
            $subject,
            $content
        );

        $mailable->assertSeeInHtml($card->title);
        $mailable->assertSeeInHtml(url("/cards/{$card->id}"));
    }
}
