<?php

namespace App\Livewire;

use App\Course;
use App\Enums\FinderItemType;
use App\Folder;
use App\Helpers\Helpers;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

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

    #[On('item-created')]
    public function handleItemCreated(): void
    {
        unset($this->foldersDestination);
    }

    protected function resetValues(): void
    {
        $this->name = '';
        $this->destination = $this->folder->id ?? null;
    }

    /**
     * Title of the component. Used in the modal view.
     *
     * @param  string  $type (\App\Enums\FinderItemType)
     */
    public function title(string $type): string
    {
        return match ($type) {
            FinderItemType::Folder => trans('folders.create'),
            FinderItemType::Card => trans('cards.create'),
        };
    }
}
