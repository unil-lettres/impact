<?php

namespace App\Livewire;

use App\Card;
use App\Course;
use App\Enums\CardBox;
use App\Enums\FinderItemType;
use App\Exceptions\CloneException;
use App\Folder;
use App\Services\Clone\CloneCardService;
use App\Services\Clone\CloneFolderService;
use App\Services\Clone\MassCloneService;
use App\Services\FinderItemsService;
use App\Services\MoveService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Finder extends Component
{
    const DEFAULT_SORT_COLUMN = 'position';

    const DEFAULT_SORT_DIRECTION = 'asc';

    /**
     * The course to display the content.
     */
    public Course $course;

    /**
     * The folder of the course to display the content.
     */
    public ?Folder $folder = null;

    /**
     * A string corresponding to the id (HTML) of the dialog to be used
     * for cloning items.
     */
    public string $modalCloneId;

    /**
     * A string corresponding to the id (HTML) of the dialog to be used
     * for moving items.
     */
    public string $modalMoveId;

    /**
     * The sort column name used to sort the content.
     */
    public string $sortColumn = self::DEFAULT_SORT_COLUMN;

    /**
     * The sort direction used to sort the content.
     */
    public string $sortDirection = self::DEFAULT_SORT_DIRECTION;

    /**
     * The filters for filtering the content.
     */
    public Collection $filters;

    /**
     * The boxes checked for the "search" filter.
     */
    public Collection $filterSearchBoxes;

    public function mount()
    {
        $this->initFilters();
    }

    public function render()
    {
        return view('livewire.finder');
    }

    #[On('item-created')]
    public function refreshItems()
    {
        unset($this->items);
    }

    #[Computed]
    public function items(): Collection
    {
        return FinderItemsService::getItems(
            Course::find($this->course->id),
            $this->filters,
            $this->filterSearchBoxes,
            $this->folder,
            $this->sortColumn,
            $this->sortDirection,
        );
    }

    #[Computed]
    public function lockedMove(): bool
    {
        return false
            || $this->sortColumn != self::DEFAULT_SORT_COLUMN
            || $this->sortDirection != self::DEFAULT_SORT_DIRECTION
            || ! $this->filters->every(fn (Collection $value) => $value->isEmpty())
            || Auth::user()->cannot('moveCardOrFolder', $this->course);
    }

    #[Computed]
    public function editors(): Collection
    {
        return $this->course->cards->map(
            fn (Card $card) => $card->editors(),
        )->flatten(1)->unique('id');
    }

    #[On('sort-updated')]
    public function move(int $id, string $type, int $position)
    {
        if ($this->lockedMove()) {
            return;
        }

        $this->authorize('moveCardOrFolder', $this->course);

        $success = $this->validateAndFlash(
            [
                'type' => $type,
                'position' => $position,
            ],
            [
                'position' => 'required|integer',
                'type' => 'required|string|in:'.FinderItemType::Card.','.FinderItemType::Folder,
            ],
        );

        if (!$success) {
            return;
        }

        $entity = $type === FinderItemType::Folder ? Folder::findOrFail($id) : Card::findOrFail($id);
        $entity->update([
            'position' => $position,
        ]);
    }

    #[On('add-element-to-filter')]
    public function addElementToFilter(mixed $filter, string $type)
    {
        $success = $this->validateAndFlash(
            [
                'type' => $type,
            ],
            [
                'type' => 'required|string|in:'.$this->filters->keys()->join(','),
            ],
        );

        if (!$success) {
            return;
        }
        $this->filters->put(
            $type,
            $this->filters->get($type)->push(
                $filter,
            )->uniqueStrict()->values(),
        );
    }

    #[On('remove-element-to-filter')]
    public function removeElementToFilter(mixed $filter, string $type)
    {
        $success = $this->validateAndFlash(
            [
                'type' => $type,
            ],
            [
                'type' => 'required|string|in:'.$this->filters->keys()->join(','),
            ],
        );

        if (!$success) {
            return;
        }

        $this->filters->put(
            $type,
            $this->filters->get($type)->filter(
                fn (mixed $_filter) => $_filter !== $filter,
            )->values(),
        );
    }

    #[On('flash-message')]
    public function flash(array $errors, string $bsClass = 'text-bg-success')
    {
        $message = collect($errors)->values()->flatten()->join('<br />');
        $this->flashMessage($message, $bsClass);
    }

    public function openFolder(Folder $folder)
    {
        return $this->redirect(route('folders.show', $folder->id));
    }

    /**
     * Add or remove a filter (search box, like name, box 2, etc.) on the card.
     */
    public function toggleFilterSearchBox(string $filter, bool $checked)
    {
        if (! in_array(
            $filter,
            ['name', CardBox::Box2, CardBox::Box3, CardBox::Box4],
        )) {
            return;
        }

        $this->filterSearchBoxes[$filter] = $checked;
    }

    public function clearFilters()
    {
        $this->initFilters();
    }

    public function sort($column, $direction): void
    {
        $success = $this->validateAndFlash(
            [
                'column' => $column,
                'direction' => $direction,
            ],
            [
                'column' => 'required|string|in:title,state_name,created_at,editors_list,tags_list,'.static::DEFAULT_SORT_COLUMN,
                'direction' => 'required|string|in:asc,desc',
            ],
        );

        if (!$success) {
            return;
        }

        $this->sortColumn = $column;
        $this->sortDirection = $direction;
    }

    public function cloneCard(Card $card): void
    {
        (new CloneCardService($card))->clone();
    }

    public function cloneFolder(
        Folder $folder,
        bool $displayFlash = false,
    ): void {
        (new CloneFolderService($folder))->clone();

        if ($displayFlash) {
            $this->flashMessage(trans('courses.finder.menu.copy.success'));
        }
    }

    public function cloneMultiple(array $keys): void
    {
        $this->keysToEntities($keys)->each(
            fn ($entity) => MassCloneService::getCloneService($entity)->clone(),
        );
    }

    public function renameFolder(
        Folder $folder,
        string $newName,
        bool $reloadAfterSave = false,
    ) {
        $success = $this->validateAndFlash(
            ['newName' => $newName],
            ['newName' => 'required|string|max:200'],
        );

        if (!$success) {
            return;
        }

        $this->authorize('update', $folder);

        $folder->update([ 'title' => $newName ]);

        if ($reloadAfterSave) {
            return $this->redirect(url()->previous());
        }
    }

    public function destroyFolder(Folder $folder, $returnToCourse = false)
    {
        $this->authorize('forceDelete', $folder);

        $folder->forceDelete();

        if ($returnToCourse) {
            return $this->redirect(route('courses.show', $folder->course->id));
        }
    }

    public function destroyCard(Card $card)
    {
        $this->authorize('forceDelete', $card);

        $card->forceDelete();
    }

    public function destroyMultiple(array $keys): void
    {
        $this->keysToEntities($keys)->each(
            function ($entity) {
                $this->authorize('forceDelete', $entity);
                $entity->forceDelete();
            }
        );
        $this->js('selectedItems = []');
    }

    public function cloneIn(array $keys, Course $dest)
    {
        try {
            MassCloneService::massCloneCardsAndFolders(
                $this->keysToEntities($keys),
                $dest,
            );
            $this->flashMessage(trans('courses.finder.clone_in.success'));
        } catch (CloneException $e) {
            $this->flashMessage($e->getMessage(), 'text-bg-danger');
        }
    }

    public function moveIn(
        array $keys,
        int $dest = null,
        bool $reloadAfterSave = false,
    ) {
        $this->authorize('moveCardOrFolder', $this->course);

        $this->keysToEntities($keys)->each(
            fn ($entity) => MoveService::moveCardOrFolder($entity, $dest ? Folder::find($dest) : null),
        );

        $this->flashMessage(trans('courses.finder.move_in.success'));

        if ($reloadAfterSave) {
            return $this->redirect(url()->previous());
        }
    }

    /**
     * Return a collection of Cards and Folders models corresponding to
     * the given keys' list.
     */
    private function keysToEntities(
        array $keys,
        $withoutDescendants = true,
    ): Collection {
        return collect($keys)
            ->map(function ($key) {
                [$type, $key] = explode('-', $key);

                return $type === FinderItemType::Card ? Card::findOrFail($key) : Folder::findOrFail($key);
            })
            ->filter(function ($entity) use ($keys, $withoutDescendants) {
                // Some times, we want only the most "oldest" parent of an item.
                // When we select a folder, all its children are automatically
                // selected. So we need to filter out the children. If not,
                // when perform a move action (or other), we will move all the
                // children instead of just the parent folder.
                return true
                    && $withoutDescendants
                    && $entity->getAncestors(false)->pluck('id')->map(
                        fn ($id) => FinderItemType::Folder."-$id",
                    )->intersect($keys)->isEmpty();
            });

    }

    private function initFilters()
    {
        $this->filterSearchBoxes = collect([
            'name' => true,
            CardBox::Box2 => false,
            CardBox::Box3 => false,
            CardBox::Box4 => false,
        ]);

        $this->filters = collect([
            'tag' => collect([]),
            'editor' => collect([]),
            'state' => collect([]),
            'search' => collect([]),
        ]);

        $jsonFilters = $this->filterSearchBoxes->toJson();
        $this->js("window.MultiFilterSelect.checkedFilter = $jsonFilters");
    }

    private function flashMessage(
        string $message,
        string $bsClass = 'text-bg-success',
    ) {
        session()->flash('message', $message);
        session()->flash('bsClass', $bsClass);

        // Hide the toast after x milliseconds.
        $this->js(<<<'JS'
           setTimeout(function(){
                document.getElementById('toast-flash').classList.remove('show');
            }, 5000);
        JS);
    }

    /**
     * Validate the array with validators and flash the first error if exists.
     */
    private function validateAndFlash(array $values, array $validators): bool
    {
        $validator = Validator::make($values, $validators);

        if ($validator->fails()) {
            $this->flashMessage(
                $validator->errors()->first(),
                'text-bg-danger',
            );

            return false;
        }

        return !empty($validator->validated());
    }
}
