<?php

namespace App\Livewire;

use App\Card;
use App\Http\Requests\UpdateCard;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CardTitle extends Component
{
    public Card $card;

    #[Validate(UpdateCard::TITLE_VALIDATION)]
    public string $title;

    public bool $editing = false;

    public function save()
    {
        $this->authorize('update', $this->card);

        $this->validate();

        $this->card->update(['title' => $this->title]);

        $this->editing = false;
    }

    public function mount(Card $card)
    {
        $this->card = $card;

        $this->fill($card->only('title'));
    }

    public function edit()
    {
        $this->editing = true;
        $this->fill($this->card->only('title'));
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.card-title');
    }
}
