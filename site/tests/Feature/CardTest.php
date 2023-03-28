<?php

namespace Tests\Feature;

use App\Card;
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
}
