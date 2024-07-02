<?php

namespace App\Livewire;

use App\Card;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class BoxEditionCounter extends Component
{
    public Card $card;

    /**
     * Used to identify the editor.
     */
    public string $reference;

    /**
     * Used to show a warning message when another user is editing the same
     * card.
     */
    public bool $concurrentEditing = false;

    /**
     * Used has a buffer to mitigate false negatives if some user have very
     * low-end connection. It wait two "cycles" before turning off the warning.
     */
    public bool $wasConcurrentEditing = false;

    public function render()
    {
        return view('livewire.box-edition-counter');
    }

    public function checkConcurrentEditing(array $editors)
    {
        /**
         * A word on how this works.
         *
         * Each editing users poll the server every x seconds to check if
         * another user is editing the same card. This is done using the
         * Laravel Cache. Each user get the cache at the key of the card he is
         * editing and replace the value by its own user id. If the value was
         * already set by another user, the current user knows that another
         * user is editing the card.
         *
         * A benefit of this approach is that the user don't have to
         * unsubscribe to anything when he leaves the page as other users will
         * overwrite his presence in the cache.
         *
         * The drawback of this approach is when the user make two subsequent
         * requests to the server (if some user have a very low-end connection)
         * it can result in a false negative. To mitigate this, we use the
         * `$wasConcurrentEditing` property to wait two "cycles" before turning
         * off the warning. The more users are editing, the less likely this
         * will happen.
         */
        $isEditing = $editors[$this->reference] ?? false;

        if ($isEditing) {
            $cachKey = "editing_{$this->reference}_{$this->card->id}";

            // The following two instructions should be atomic. It can cause some
            // false negatives in some cases that we accept has it is a very low
            // probability event and don't have meaningful impact.
            $lastUser = Cache::get($cachKey);
            Cache::put($cachKey, Auth::user()->id, 5);

            $anotherIsEditing = $lastUser && $lastUser !== Auth::user()->id;
            $this->concurrentEditing = $anotherIsEditing || $this->wasConcurrentEditing;
            $this->wasConcurrentEditing = $anotherIsEditing;
        } else {
            $this->concurrentEditing = $this->wasConcurrentEditing = false;
        }
    }
}
