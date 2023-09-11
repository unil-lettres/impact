<?php

namespace App\Livewire;

use App\Card;
use Illuminate\Contracts\Support\Renderable;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Media extends Component
{
    #[Rule('required')]
    public Card $card;

    #[Rule('required')]
    public string $reference;

    #[Rule('required')]
    public bool $mediaStatusIsActive;

    public function mount(Card $card, string $reference)
    {
        $this->card = $card;
        $this->reference = $reference;
        $this->mediaStatusIsActive = false;
    }

    /*
     * Used to determine if the MediaStatus
     * Livewire component is active or not.
     */
    #[On('media-status-is-active')]
    public function setMediaStatusIsActive(bool $active): void
    {
        $this->mediaStatusIsActive = $active;
    }

    /*
     * Used to determine the cache key for
     * the MediaStatus Livewire component.
     */
    public function mediaStatusKey(): string
    {
        if (! $this->card->file) {
            return 'no-file';
        }

        return $this->card->file->status.'-'.$this->card->file->progress ?? '0';
    }

    public function render(): Renderable
    {
        return view('livewire.media');
    }
}
