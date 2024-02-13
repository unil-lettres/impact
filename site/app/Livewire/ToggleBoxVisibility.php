<?php

namespace App\Livewire;

use App\Card;
use App\Enums\CardBox;
use InvalidArgumentException;
use Livewire\Component;

class ToggleBoxVisibility extends Component
{
    public Card $card;

    public string $box;

    public function mount(string $box)
    {
        if (! CardBox::getAllBoxes()->contains($box)) {
            throw new InvalidArgumentException();
        }
        $this->box = $box;
    }

    public function toggle()
    {
        $this->authorize('update', $this->card);

        $toggledHidden = ! $this->card->options[$this->box]['hidden'];

        $this->card->update(["options->$this->box->hidden" => $toggledHidden]);

        $isHidden = $toggledHidden ? 'true' : 'false';

        $this->js(<<<JS
            if ($isHidden) {
                $(".card.$this->box").addClass('hidden');

                if ($('#btn-hide-boxes').hasClass('enabled')) {
                    $(".card.$this->box").show();
                } else {
                    $(".card.$this->box").hide();
                }
            } else {
                $(".card.$this->box").removeClass('hidden');
                $(".card.$this->box").show();
            }
        JS);
    }

    public function render()
    {
        return view('livewire.toggle-box-visibility');
    }
}
