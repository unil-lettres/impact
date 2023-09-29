<?php

namespace App\Observers;

use App\Card;
use App\Course;
use App\Enums\ActionType;
use App\Enums\StateType;
use App\Helpers\Helpers;
use App\Mail\StateSelected;
use Illuminate\Support\Facades\Mail;

class CardObserver
{
    /**
     * Handle the Card "created" event.
     *
     * @return void
     */
    public function created(Card $card)
    {
        $cardUpdate = [];

        if (is_null($card->position)) {
            $cardUpdate['position'] = Helpers::findLastPositionInParent($card);
        }

        // If state is not set, set it the private state
        if (! $card->state) {
            $state = $card
                ->course
                ->states->where(
                    'type', StateType::Private
                )->first();

            $cardUpdate['state_id'] = $state->id;
        }

        $card->updateQuietly($cardUpdate);
    }

    /**
     * Handle the Card "updated" event.
     */
    public function updated(Card $card): void
    {
        // Relations that were already loaded before the update will still
        // have the old values.
        $card->refresh();

        if ($card->wasChanged('folder_id')) {
            $card->updateQuietly([
                'position' => Helpers::findLastPositionInParent($card),
            ]);
        }

        // Check if the state of the card has changed and if was already set
        if ($card->wasChanged('state_id') && $card->getOriginal('state_id')) {
            // Loop through the actions of the new state
            foreach ($card->state->getActionsData() as $action) {
                switch ($action['type']) {
                    case ActionType::Email:
                        // Cancel if the card has the do not sent
                        // emails option set to true
                        if ($card->options['no_emails']) {
                            return;
                        }

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
     */
    private function sendEmailAction(Card $card, array $action): void
    {
        // Send the state changed email to the teachers of the course
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
