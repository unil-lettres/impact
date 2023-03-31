<?php

namespace App\Observers;

use App\Card;
use App\Enums\ActionType;
use App\Mail\StateSelected;
use Illuminate\Support\Facades\Mail;

class CardObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    //public $afterCommit = true;

    /**
     * Handle the Card "updated" event.
     *
     * @param  Card  $card
     * @return void
     */
    public function updated(Card $card)
    {
        // Check if the state of the card has changed
        if ($card->wasChanged('state_id')) {
            // Loop through the actions of the new state
            foreach ($card->state?->getActionsData() as $action) {
                switch ($action['type']) {
                    case ActionType::Email:
                        $this->sendEmailAction(
                            $card,
                            $action
                        );
                        break;
                    case ActionType::None:
                        break;
                }
            }
        }
    }

    /**
     * Send an email to the teachers of the course
     *
     * @param  Card  $card
     * @param  array  $action
     * @return void
     */
    private function sendEmailAction(Card $card, array $action)
    {
        // Send the state changed email to
        // the teachers of the course
        Mail::to(
            $card->course->teachers()->map(function ($teacher) {
                return $teacher->email;
            })
        )->send(
            new StateSelected(
                $card,
                '[Impact] '.$action['subject'],
                $action['message']
            )
        );
    }
}
