<?php

namespace App\Observers;

use App\Card;
use App\Enums\ActionType;
use App\Enums\StateType;
use App\Mail\StateSelected;
use App\Traits\FindLastPosition;
use Illuminate\Support\Facades\Mail;

class CardObserver
{
    use FindLastPosition;

    /**
     * Handle the Card "created" event.
     */
    public function created(Card $card): void
    {
        $cardUpdate = [];

        // Set the last available position for this card in its parent.
        if (is_null($card->position)) {
            $cardUpdate['position'] = $this->findLastPositionInParent($card);
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

        // We update quietly since we don't want to trigger the updated event
        // to avoid recalculation of the position.
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
            // We update quietly to avoid recursion.
            $card->updateQuietly([
                'position' => $this->findLastPositionInParent($card),
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
     * Handle the "forceDeleting" event.
     */
    public function forceDeleting(Card $card): void
    {
        // Delete attachments (only attachments and not regular file).
        $card->attachments()->each(
            fn ($attachment) => $attachment->forceDelete(),
        );

        // Remove this card from all enrollments as editors.
        $card->enrollments()->each(
            fn ($enrollment) => $enrollment->removeCard($card),
        );
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
