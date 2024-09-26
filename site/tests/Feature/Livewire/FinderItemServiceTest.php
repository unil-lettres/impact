<?php

namespace Tests\Feature\Livewire;

use App\Card;
use App\Course;
use App\Enums\CardBox;
use App\Enums\TranscriptionType;
use App\Services\FinderItemsService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FinderItemServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->course = Course::factory()->create([
            'transcription' => TranscriptionType::Icor,
        ]);
    }

    protected function getItemsHelperForSearch(array $terms, string $box): Collection
    {
        return FinderItemsService::getItems(
            $this->course,
            collect([
                'tag' => collect([]),
                'holder' => collect([]),
                'state' => collect([]),
                'search' => collect($terms),
            ]),
            [
                'name' => $box === 'name',
                CardBox::Box2 => $box === 'box2',
                CardBox::Box3 => $box === 'box3',
                CardBox::Box4 => $box === 'box4',
            ],
        );
    }

    public function testSearch(): void
    {
        Card::factory()->create([
            'course_id' => $this->course->id,
            'box2' => json_decode('{"version":2,"icor":[{"number":1,"speaker":"TES","speech":"Ceci est un test pour la recherche: afrutre.","linkedToPrevious":false}],"text":"<p>Ceci est un test pour la recherche: aCn  fh.<\/p>"}'),
            'box3' => 'aaa jszencl aaa',
            'title' => 'K oala',
        ]);

        Card::factory()->create([
            'course_id' => $this->course->id,
            'box2' => json_decode('{"version":2,"icor":[{"number":1,"speaker":"TES","speech":"Ceci est un test pour la recherche: acfgea.","linkedToPrevious":false}],"text":"<p>Ceci est un test pour la recherche: a2Cn  fh.<\/p>"}'),
        ]);

        // Search in name (case insensitiv and trailing whitespace).
        $items = $this->getItemsHelperForSearch(['kOa La'], 'name');
        $this->assertCount(1, $items);

        // Search in box3 (case insensitiv and trailing whitespace).
        $items = $this->getItemsHelperForSearch(['ajszencla'], CardBox::Box3);
        $this->assertCount(1, $items);

        // Search in ICOR.
        $items = $this->getItemsHelperForSearch(['afrutre'], CardBox::Box2);
        $this->assertCount(1, $items);

        // Search in ICOR for multiple terms.
        $items = $this->getItemsHelperForSearch(['afrutre', 'acfgea'], CardBox::Box2);
        $this->assertCount(2, $items);

        // Search in transcription plain text (case insensitiv and trailing whitespace).
        $this->course->transcription = TranscriptionType::Text;
        $this->course->save();
        $this->course->refresh();
        $items = $this->getItemsHelperForSearch(['a  cnf h'], CardBox::Box2);
        $this->assertCount(1, $items);
    }
}
