<?php

namespace App\Livewire;

use App\Card;
use App\Enrollment;
use App\Rules\Holders;
use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class ModalCreateCard extends ModalCreate
{
    /**
     * List of users able to edit a card.
     */
    public array $holders = [];

    public function render()
    {
        return view('livewire.modal-create-card');
    }

    public function rules()
    {
        return [
            'name' => 'required|min:1|max:255',
            'holders' => [
                'required',
                'array',
                new Holders,
            ],
            'destination' => [
                'nullable',
                'integer',
                // Parent folder must be in the current course.
                Rule::exists('folders', 'id')->where(
                    fn (Builder $query) => $query->where(
                        'course_id',
                        $this->course->id,
                    )
                ),
            ],
        ];
    }

    #[On('add-holder')]
    public function addHolder(int $id, string $name): void
    {
        $this->holders = Arr::add($this->holders, $id, $name);

        // Skip the re-render of the component to avoid
        // the modal form to disappear.
        $this->skipRender();
    }

    #[On('remove-holder')]
    public function removeHolder(int $id, string $name): void
    {
        if (Arr::exists($this->holders, $id)) {
            $this->holders = Arr::except($this->holders, [$id]);
        }

        // Skip the re-render of the component to avoid
        // the modal form to disappear.
        $this->skipRender();
    }

    public function enrolledUsers(): Collection
    {
        // Return the list of users enrolled in the course.
        return Enrollment::with('user')
            ->where('course_id', $this->course->id)
            ->get()
            ->map(fn (Enrollment $enrollment) => $enrollment->user)
            ->unique('id');
    }

    public function resetHolders(bool $skipRender = false): void
    {
        // We reset the holders property separately because
        // it is called when the modal is shown.
        $this->holders = [];

        if ($skipRender) {
            // Skip the re-render of the component to avoid
            // the modal form to disappear.
            $this->skipRender();
        }
    }

    /**
     * Called when form submitted for creating the card.
     */
    public function create()
    {
        $this->validate();

        $this->authorize('create', [Card::class, $this->course]);

        $card = Card::create([
            'title' => $this->name,
            'course_id' => $this->course->id,
            'folder_id' => $this->destination,
        ]);

        // Make selected users holders of the card
        foreach ($this->holders as $id => $name) {
            $enrollment = Enrollment::where('course_id', $this->course->id)
                ->where('user_id', $id)
                ->first();

            $enrollment->addCard($card);
        }

        $this->resetValues();
        $this->resetHolders();

        // We need to dispatch events to other components
        // to update their list of folders & items.
        $this
            ->dispatch('items-updated')
            ->to(Finder::class); // Already triggered by validate(), but we keep it for clarity.
    }
}
