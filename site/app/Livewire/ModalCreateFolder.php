<?php

namespace App\Livewire;

use App\Folder;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class ModalCreateFolder extends ModalCreate
{
    public function render()
    {
        return view('livewire.modal-create-folder');
    }

    public function rules()
    {
        return [
            'name' => 'required|min:1|max:255',
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

    /**
     * Called when form submitted for creating the folder.
     */
    public function create()
    {
        $this->validate();

        $this->authorize('create', [Folder::class, $this->course]);

        Folder::create([
            'title' => $this->name,
            'course_id' => $this->course->id,
            'parent_id' => $this->destination,
        ]);

        $this->resetValues();

        // We need to dispatch events to other components
        // to update their list of folders & items.
        $this
            ->dispatch('items-updated')
            ->to(Finder::class); // Already triggered by validate(), but we keep it for clarity.
        $this
            ->dispatch('update-folders')
            ->to(ModalCreateCard::class);
    }
}
