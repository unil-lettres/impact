<?php

namespace App\Livewire;

use App\Card;
use App\Enums\FileStatus;
use App\Helpers\Helpers;
use Illuminate\Contracts\Support\Renderable;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Media extends Component
{
    #[Validate('required')]
    public Card $card;

    #[Validate('required')]
    public string $reference;

    #[Validate('required')]
    public bool $mediaStatusIsActive = false;

    /*
     * Return whether the card should show the media status
     * Livewire component in box1
     */
    public function showMediaStatus(Card $card)
    {
        $show = $this->shouldShowMediaStatus($card);

        if ($show) {
            // If the media status view is shown, we want
            // Livewire refresh to show it until the file
            // is ready and the page is reloaded manually
            $this->mediaStatusIsActive = true;
        }

        return $show || $this->mediaStatusIsActive;
    }

    /*
     * Used to generate the cache key for the MediaStatus
     * Livewire component
     */
    public function mediaStatusKey(): string
    {
        if (! $this->card->file) {
            return 'no-file';
        }

        return $this->card->file->status.'-'.$this->card->file->progress ?? '0';
    }

    #[On('card-udpate')]
    public function cardUpdate(): void
    {
        $this->card->refresh();
        $this->card->loadMissing('file');

        $this->js("window.CardPlayer.refresh($this->card)");
    }

    public function render(): Renderable
    {
        return view('livewire.media');
    }

    private function shouldShowMediaStatus(Card $card): bool
    {
        // Show media status if the card doesn't have a file yet
        if (! Helpers::cardHasSource($card)) {
            return true;
        }

        // Do not show media status if the card has an external link
        if (Helpers::cardHasExternalLink($card)) {
            return false;
        }

        // Show media status if the card has a file that is not ready
        if (! Helpers::isFileStatus($card->file, FileStatus::Ready)) {
            return true;
        }

        return false;
    }
}
