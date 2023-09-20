<?php

namespace App\Livewire;

use App\Card;
use Illuminate\Contracts\Support\Renderable;
use Livewire\Attributes\Rule;
use Livewire\Component;

class MediaStatus extends Component
{
    #[Rule('required')]
    public Card $card;

    #[Rule('required')]
    public int $progress;

    public function mount(Card $card)
    {
        $this->card = $card;
        $this->progress = $card->file?->progress ?? 0;
    }

    public function render(): Renderable
    {
        return view('livewire.media-status');
    }
}
