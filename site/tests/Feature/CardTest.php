<?php

namespace Tests\Feature;

use App\Card;
use App\Course;
use App\Services\Clone\CloneCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCardCanBeCreated()
    {
        $cardData = [
            'title' => fake()->title,
        ];

        $card = Card::factory()->create($cardData);

        $this->assertInstanceOf(Card::class, $card);
        $this->assertDatabaseHas('cards', $cardData);
    }

    public function testCardCanBeUpdated()
    {
        $card = Card::factory()->create();

        $cardDataUpdated = [
            'title' => 'Updated Test Card Title',
        ];

        $card->update($cardDataUpdated);

        $this->assertDatabaseHas('cards', $cardDataUpdated);
    }

    public function testCardCanBeDeleted()
    {
        $card = Card::factory()->create();

        $card->delete();

        $this->assertSoftDeleted($card);
    }

    public function testCardPositionCorrectlyInitialized()
    {
        $course = Course::factory()->hasCards(3)->hasFolders(7)->create();

        $this->assertEquals(0, $course->cards->first()->position);
        $this->assertEquals(2, $course->cards->last()->position);
        $this->assertEquals(3, $course->folders->first()->position);
        $this->assertEquals(9, $course->folders->last()->position);
    }

    public function testCloneCardInsideFolder()
    {
        $course = Course::factory()->hasFolders(1)->hasCards(1)->create();
        $folder = $course->folders->first();
        $card = $course->cards->first();

        $clonedCard = (new CloneCardService($card))->clone($folder);

        $this->assertEquals($card->title, $clonedCard->title);
        $this->assertEquals($clonedCard->folder_id, $folder->id);
        $this->assertEquals($clonedCard->course_id, $course->id);
    }
}
