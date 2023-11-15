<?php

namespace App\Livewire;

use App\Card;
use App\Course;
use App\Enums\FinderItemType;
use App\Folder;
use App\Helpers\Helpers;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\On;

class ModalCreate extends Component
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
     * The type of item to create.
     * Must be one of App\Enums\FinderItemType "enum".
     */
    public string $type;

    /**
     * The folder from which the dialog is called.
     * Null if called from course.
     */
    public ?Folder $folder = null;

    /**
     * Name of the folder to create.
     */
    public string $name = '';

    /**
     * Destination Folder id to create the item in.
     */
    public ?int $destination = null;

    public function mount()
    {
        $this->resetValues();
    }

    public function render()
    {
        return view('livewire.modal-create');
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
    public function title()
    {
        return match ($this->type) {
            FinderItemType::Folder => trans('folders.create'),
            FinderItemType::Card => trans('cards.create'),
        };
    }

    #[Computed]
    public function foldersDestination()
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

    #[On('item-created')]
    public function handleItemCreated(): void
    {
        unset($this->foldersDestination);
    }

    public function resetValues()
    {
        $this->name = '';
        $this->destination = $this->folder->id ?? null;
    }

    /**
     * Called when form submitted for creating the item.
     */
    public function create()
    {
        $this->validate();
        if ($this->type == FinderItemType::Card) {
            $this->authorize('create', [Card::class, $this->course]);

            Card::create([
                'title' => $this->name,
                'course_id' => $this->course->id,
                'folder_id' => $this->destination,
            ]);
        } elseif ($this->type === FinderItemType::Folder) {
            $this->authorize('create', [Folder::class, $this->course]);

            Folder::create([
                'title' => $this->name,
                'course_id' => $this->course->id,
                'parent_id' => $this->destination,
            ]);
        }

        $this->resetValues();

        // We need to dispatch this event to all other ModalCreate components to
        // update their list of destinations folders.
        $this
            ->dispatch('item-created')
            ->to(Finder::class)
            ->to(ModalCreate::class);
    }
}
