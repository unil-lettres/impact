<?php

namespace App\Livewire;

use App\Card;
use Livewire\Component;

class ToggleSourceSync extends Component
{
    public Card $card;

    public function toggle()
    {
        $this->authorize('update', $this->card);

        $toggledSync = ! $this->card->options['box2']['sync'];

        $this->card->update(['options->box2->sync' => $toggledSync]);

        $this->dispatch('card-udpate')->to(Media::class);
    }

    public function render()
    {
        return view('livewire.toggle-source-sync');
    }
}
