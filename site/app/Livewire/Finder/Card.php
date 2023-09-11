<?php

namespace App\Livewire\Finder;

use App\Card as AppCard;
use Livewire\Attributes\On;
use Livewire\Component;

class Card extends Component
{
    public AppCard $card;
    public $depth = 0;

    #[On('sort-updated')]
    public function move(int $position)
    {
        $this->card->position = $position;
        $this->card->save();
    }

    public function render()
    {
        return view('livewire.finder.card');
    }
}
