<?php

namespace App\Livewire;

use App\Card;
use App\Course;
use App\Enrollment;
use App\Folder;
use App\Helpers\Helpers;
use App\Rules\Editors;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ModalCreateCard extends Component
{
    /**
     * An id (HTML) to identify the dialog.
     */
    public $id;

    /**
     * The course in which create the item.
     */
    public Course $course;

    /**
     * The folder from which the dialog is called.
     * Null if called from course.
     */
    public ?Folder $folder = null;

    /**
     * Name of the card to create.
     */
    public string $name = '';

    /**
     * Destination Folder id to create the item in.
     */
    public ?int $destination = null;

    /**
     * List of users able to edit a card.
     */
    public array $editors = [];

    public function mount()
    {
        $this->resetValues();
    }

    public function render()
    {
        return view('livewire.modal-create-card');
    }

    public function boot()
    {
        // Add after event on validator to display flash message on Finder
        // component.
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if (! empty($validator->errors())) {
                    $this
                        ->dispatch(
                            'flash-message',
                            $validator->errors(),
                            'text-bg-danger',
                        )
                        ->to(Finder::class);
                }
            });
        });
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

    #[Computed]
    public function foldersDestination(): Collection
    {
        $children =
            $this->folder
            ? $this->folder->getChildrenRecursive()
            : $this->course->folders;

        return Helpers::getFolderListAbsolutePath(
            $children,
            $this->folder,
        )->sortBy('titleFullPath');
    }

    #[Computed]
    public function enrolledUsers(): Collection
    {
        // Return the list of users enrolled in the course.
        return $this->course->enrollments->map(
            fn (Enrollment $enrollment) => $enrollment->user,
        )->flatten(1)->unique('id');
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

    #[On('item-created')]
    public function handleItemCreated(): void
    {
        unset($this->foldersDestination);
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
     * Called when form submitted for creating the item.
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

        foreach ($this->editors as $id => $name) {
            $enrollment = Enrollment::where('course_id', $this->course->id)
                ->where('user_id', $id)
                ->first();

            $enrollment->addCard($card);
        }

        $this->resetValues();
        $this->resetEditors();

        // We need to dispatch this event to all other ModalCreate components to
        // update their list of destinations folders.
        $this
            ->dispatch('item-created')
            ->to(Finder::class)
            ->to(ModalCreateCard::class);
    }

    private function resetValues(): void
    {
        $this->name = '';
        $this->destination = $this->folder->id ?? null;
    }
}
