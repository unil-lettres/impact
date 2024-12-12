<?php

namespace Tests\Feature;

use App\Card;
use App\Course;
use App\Folder;
use App\Services\Clone\CloneCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_card_can_be_created(): void
    {
        $cardData = [
            'title' => fake()->title,
        ];

        $card = Card::factory()->create($cardData);

        $this->assertInstanceOf(Card::class, $card);
        $this->assertDatabaseHas('cards', $cardData);
    }

    public function test_card_can_be_updated(): void
    {
        $card = Card::factory()->create();

        $cardDataUpdated = [
            'title' => 'Updated Test Card Title',
        ];

        $card->update($cardDataUpdated);

        $this->assertDatabaseHas('cards', $cardDataUpdated);
    }

    public function test_card_can_be_deleted(): void
    {
        $card = Card::factory()->create();

        $card->delete();

        $this->assertSoftDeleted($card);
    }

    public function test_card_position_correctly_initialized(): void
    {
        $course = Course::factory()->create();

        // We create sequentially entities for the observer to set the position
        // correctly.

        for ($i = 0; $i < 3; $i++) {
            $card = Card::factory()->for($course)->create();
            $this->assertEquals($i, $card->position);
        }

        for (; $i < 7; $i++) {
            $folder = Folder::factory()->for($course)->create();
            $this->assertEquals($i, $folder->position);
        }
    }

    public function test_clone_card_inside_folder(): void
    {
        $course = Course::factory()->hasFolders(1)->hasCards(1)->create();
        $folder = $course->folders->first();
        $card = $course->cards->first();

        $clonedCard = (new CloneCardService($card))->clone($folder);

        $this->assertEquals($card->title, $clonedCard->title);
        $this->assertEquals($clonedCard->folder_id, $folder->id);
        $this->assertEquals($clonedCard->course_id, $course->id);
    }

    public function test_icor_version_max_length(): void
    {
        $card = Card::factory()->create();

        $this->assertEquals($card->getMaxCharactersByLine(), Card::MAX_CHARACTERS_SPEECH);

        $card = Card::factory()->transcriptionVersion(1)->create();

        $this->assertEquals($card->getMaxCharactersByLine(), Card::MAX_CHARACTERS_LEGACY_SPEECH);
    }
}
