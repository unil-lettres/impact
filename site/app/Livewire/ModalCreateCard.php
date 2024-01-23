<?php

namespace App\Livewire;

use App\Card;
use App\Enrollment;
use App\Rules\Editors;
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
    public array $editors = [];

    public function render()
    {
        return view('livewire.modal-create-card');
    }

    public function rules()
    {
        return [
            'name' => 'required|min:1|max:255',
            'editors' => [
                'required',
                'array',
                new Editors,
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

    #[On('add-editor')]
    public function addEditor(int $id, string $name): void
    {
        $this->editors = Arr::add($this->editors, $id, $name);

        // Skip the re-render of the component to avoid
        // the modal form to disappear.
        $this->skipRender();
    }

    #[On('remove-editor')]
    public function removeEditor(int $id, string $name): void
    {
        if (Arr::exists($this->editors, $id)) {
            $this->editors = Arr::except($this->editors, [$id]);
        }

        // Skip the re-render of the component to avoid
        // the modal form to disappear.
        $this->skipRender();
    }

    public function enrolledUsers(): Collection
    {
        // Return the list of users enrolled in the course.
        return $this->course->enrollments->map(
            fn (Enrollment $enrollment) => $enrollment->user,
        )->flatten(1)->unique('id');
    }

    public function resetEditors(bool $skipRender = false): void
    {
        // We reset the editors property separately because
        // it is called when the modal is shown.
        $this->editors = [];

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

        // Make selected users editors of the card
        foreach ($this->editors as $id => $name) {
            $enrollment = Enrollment::where('course_id', $this->course->id)
                ->where('user_id', $id)
                ->first();

            $enrollment->addCard($card);
        }

        $this->resetValues();
        $this->resetEditors();

        // We need to dispatch events to other components
        // to update their list of folders & items.
        $this
            ->dispatch('items-updated')
            ->to(Finder::class); // Already triggered by validate(), but we keep it for clarity.
    }
}
