<?php

namespace App\Livewire;

use App\Card;
use App\Course;
use App\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Livewire\Component;

class ModalUpdateState extends Component
{
    /**
     * HTML id of the modal.
     */
    public string $id;

    /**
     * The course in which create the item.
     */
    public Course $course;

    /**
     * The cards to update.
     *
     * @var Collection<Card>
     */
    public Collection $cards;

    /**
     * The form field state value.
     */
    public ?int $state = null;

    public function mount()
    {
        $this->cards = collect([]);
    }

    public function render()
    {
        return view('livewire.modal-update-state');
    }

    /**
     * Initialize the properties.
     *
     * @param array<int> $cardsId
     */
    public function init(array $cardsId, int $stateId = null)
    {
        $this->cards = collect(
            array_map(fn ($id) => Card::findOrFail($id), $cardsId),
        );
        $this->state = $stateId;

        // Skip the re-render of the component to avoid the modal form to
        // disappear.
        $this->skipRender();
    }

    public function update()
    {
        // Check that the state and cards belongs to the course.
        $invalid = !$this->course->states->pluck('id')->contains($this->state);

        if (is_null($this->state)) {
            $this
                ->dispatch(
                    'flash-message',
                    [trans('messages.update.no_effect')],
                    'text-bg-warning',
                )
                ->to(Finder::class);
            return;
        }

        $invalid |= $this->cards->filter(
            fn ($card) => $card->course_id !== $this->course->id
        )->isNotEmpty();

        if ($invalid) {
            $this
                ->dispatch(
                    'flash-message',
                    [trans('messages.error.refresh')],
                    'text-bg-danger',
                )
                ->to(Finder::class);
            return;
        }

        try {
            $this->cards->each(fn($card) => $this->authorize('update', $card));
        } catch(AuthorizationException) {
            $this
                ->dispatch(
                    'flash-message',
                    [trans('courses.finder.dialog.update_state.not_authorized')],
                    'text-bg-danger',
                )
                ->to(Finder::class);
            return;
        }

        // Update the state for each cards.
        $this->cards->each(function($card) {
            $card->state_id = $this->state;
            $card->save();
        });

        $this
            ->dispatch(
                'flash-message',
                [trans('messages.state.updated')],
                'text-bg-success',
            )
            ->to(Finder::class);

        // // Tell the Finder component that a item has been updated.
        $this
            ->dispatch('items-updated')
            ->to(Finder::class);
    }
}
