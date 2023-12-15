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
use Livewire\Attributes\Url;
use Livewire\Component;

class Finder extends Component
{
    const DEFAULT_SORT_COLUMN = 'position';

    const DEFAULT_SORT_DIRECTION = 'asc';

    const DEFAULT_SEARCH_BOX_NAME = true;

    const DEFAULT_SEARCH_BOX_2 = false;

    const DEFAULT_SEARCH_BOX_3 = false;

    const DEFAULT_SEARCH_BOX_4 = false;

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
    #[Url(as: 'sc')]
    public string $sortColumn = self::DEFAULT_SORT_COLUMN;

    /**
     * The sort direction used to sort the content.
     */
    #[Url(as: 'sd')]
    public string $sortDirection = self::DEFAULT_SORT_DIRECTION;

    /**
     * The filters for filtering the content.
     */
    #[Url(as: 'q')]
    public array $arrayFilters;

    /**
     * The boxes checked for the "search" filter.
     */
    #[Url(as: 'b')]
    public array $filterSearchBoxes = [
        'name' => self::DEFAULT_SEARCH_BOX_NAME,
        CardBox::Box2 => self::DEFAULT_SEARCH_BOX_2,
        CardBox::Box3 => self::DEFAULT_SEARCH_BOX_3,
        CardBox::Box4 => self::DEFAULT_SEARCH_BOX_4,
    ];

    public function mount()
    {
        // Query string return "true" or "false" as string, we need to convert it.
        $this->filterSearchBoxes = collect($this->filterSearchBoxes)->map(
            fn ($checked) => $checked === 'true' || $checked === true
        )->toArray();

        // Initialize keys that are not presents in query string.
        // Need to map the values of query string to int for ids.
        $mapToInt = fn ($key) => array_map(
            fn ($id) => (int) $id, $this->arrayFilters[$key] ?? []
        );
        $this->arrayFilters = [
            'tag' => $mapToInt('tag'),
            'editor' => $mapToInt('editor'),
            'state' => $mapToInt('state'),
            'search' => $this->arrayFilters['search'] ?? [],
        ];

        $this->addJsForFilters();
    }

    public function render()
    {
        return view('livewire.finder');
    }

    #[On('item-created')]
    public function refreshItems(): void
    {
        unset($this->items);
    }

    #[Computed]
    public function filters(): Collection
    {
        return collect($this->arrayFilters)
            ->map(fn ($value) => collect($value));
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
            // Should be the default sort order.
            || $this->sortColumn != self::DEFAULT_SORT_COLUMN
            || $this->sortDirection != self::DEFAULT_SORT_DIRECTION

            // No filter must be selected.
            || ! $this->filters->every(
                fn (Collection $value) => $value->isEmpty()
            )

            // Must have the authorization.
            || Auth::user()->cannot(
                'massActionsForCardAndFolder',
                $this->course,
            );
    }

    #[Computed]
    public function editors(): Collection
    {
        // Return the list of editors for all cards of the course.
        return $this->course->cards->map(
            fn (Card $card) => $card->editors(),
        )->flatten(1)->unique('id');
    }

    #[On('sort-updated')]
    public function move(int $id, string $type, int $position): void
    {
        if ($this->lockedMove()) {
            return;
        }

        $this->authorize('massActionsForCardAndFolder', $this->course);

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

        if (! $success) {
            return;
        }

        $entity = (
            $type === FinderItemType::Folder
            ? Folder::findOrFail($id)
            : Card::findOrFail($id)
        );

        $entity->update([
            'position' => $position,
        ]);
    }

    #[On('add-element-to-filter')]
    public function addElementToFilter(mixed $filter, string $type): void
    {
        $success = $this->validateAndFlash(
            [
                'type' => $type,
            ],
            [
                'type' => 'required|string|in:'.$this->filters->keys()->join(','),
            ],
        );

        if (! $success) {
            return;
        }

        $this->arrayFilters[$type] = $this->filters
            ->get($type)
            ->push($filter)
            ->uniqueStrict()
            ->values()
            ->toArray();

        unset($this->filters);
    }

    #[On('remove-element-to-filter')]
    public function removeElementToFilter(mixed $filter, string $type): void
    {
        $success = $this->validateAndFlash(
            [
                'type' => $type,
            ],
            [
                'type' => 'required|string|in:'.$this->filters->keys()->join(','),
            ],
        );

        if (! $success) {
            return;
        }

        $this->arrayFilters[$type] = $this->filters
            ->get($type)
            ->filter(fn (mixed $_filter) => $_filter !== $filter)
            ->values()
            ->toArray();

        unset($this->filters);
    }

    #[On('flash-message')]
    public function flash(
        array $lines,
        string $bsClass = 'text-bg-success',
    ): void {
        $message = collect($lines)->values()->flatten()->join('<br />');
        $this->flashMessage($message, $bsClass);
    }

    /**
     * Return an array of items to be given to react multi select to
     * initialize options and defaults values.
     */
    public function filterSearchOptions(): array
    {
        return $this->filters->get('search')->map(
            fn ($search) => ['id' => $search, 'name' => $search],
        )->toArray();
    }

    public function openFolder(Folder $folder): void
    {
        $this->redirect(route('folders.show', $folder->id));
    }

    /**
     * Add or remove a filter (search box, like name, box 2, etc.) on the card.
     */
    public function toggleFilterSearchBox(string $filter, bool $checked): void
    {
        $success = $this->validateAndFlash(
            [
                'type' => $filter,
            ],
            [
                'type' => 'required|string|in:name,'.CardBox::Box2.','.CardBox::Box3.','.CardBox::Box4,
            ],
        );
        if (! $success) {
            return;
        }

        $this->filterSearchBoxes[$filter] = $checked;
    }

    /**
     * Reinitialise all filters and sort to their default values.
     */
    public function clearFiltersAndSort(): void
    {
        $this->reinitFilters();

        $this->sortColumn = static::DEFAULT_SORT_COLUMN;
        $this->sortDirection = static::DEFAULT_SORT_DIRECTION;
    }

    /**
     * Change the sorting column and direction.
     */
    public function sort(string $column, string $direction): void
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

        if (! $success) {
            return;
        }

        $this->sortColumn = $column;
        $this->sortDirection = $direction;
    }

    /**
     * Clone a card.
     */
    public function cloneCard(Card $card): void
    {
        $this->authorize('manage', $card);

        (new CloneCardService($card))->clone();
    }

    /**
     * Clone a folder. If $displayFlash is true, a flash message will be
     * displayed when the folder is cloned.
     */
    public function cloneFolder(
        Folder $folder,
        bool $displayFlash = false,
    ): void {
        // We do not check folder's children since it's the same as the parent.
        $this->authorize('manage', $folder);

        (new CloneFolderService($folder))->clone();

        $this->dispatchToModals();

        if ($displayFlash) {
            $this->flashMessage(trans('courses.finder.menu.copy.success'));
        }
    }

    /**
     * Clone all items in the $keys array ($keys is 'card-1', 'folder-2', etc.).
     */
    public function cloneMultiple(array $keys): void
    {
        $this->keysToEntities($keys)
            ->each(fn ($item) => $this->authorize('manage', $item))
            ->each(fn ($entity) => MassCloneService::getCloneService($entity)->clone());

        $this->dispatchToModals();
    }

    /**
     * Rename a folder. Reload the page if $reloadAfterSave is true (useful for
     * update the folder's name if not reactive).
     */
    public function renameFolder(
        Folder $folder,
        string $newName,
        bool $reloadAfterSave = false,
    ): void {
        $success = $this->validateAndFlash(
            ['newName' => $newName],
            ['newName' => 'required|string|max:200'],
        );

        if (! $success) {
            return;
        }

        $this->authorize('update', $folder);

        $folder->update(['title' => $newName]);

        $this->dispatchToModals();

        if ($reloadAfterSave) {
            $this->redirect(url()->previous());
        }
    }

    /**
     * Delete the folder and all it's content recursively.
     * If $returnToCourse is true, redirect to the course page after deletion (
     * useful to avoid an error 404 if the user is on the folder's page).
     */
    public function destroyFolder(
        Folder $folder,
        bool $returnToCourse = false,
    ): void {
        $this->authorize('forceDelete', $folder);

        $folder->forceDelete();

        $this->dispatchToModals();

        if ($returnToCourse) {
            $this->redirect(route('courses.show', $folder->course->id));
        }
    }

    /**
     * Delete the card.
     */
    public function destroyCard(Card $card): void
    {
        $this->authorize('forceDelete', $card);

        $card->forceDelete();
    }

    /**
     * Delete all items in the $keys array ($keys is 'card-1', 'folder-2', etc.).
     */
    public function destroyMultiple(array $keys): void
    {
        $this->keysToEntities($keys)->each(
            function ($entity) {
                $this->authorize('forceDelete', $entity);
                $entity->forceDelete();
            }
        );

        $this->dispatchToModals();

        $this->js('selectedItems = []');
    }

    /**
     * Clone all items in the $keys array ($keys is 'card-1', 'folder-2', etc.).
     */
    public function cloneIn(array $keys, Course $dest): void
    {
        if (! Auth::user()->isTeacher($dest)) {
            abort(403);
        }

        $items = $this->keysToEntities($keys);
        $items->each(fn ($item) => $this->authorize('manage', $item));

        try {
            MassCloneService::massCloneCardsAndFolders($items, $dest);
            $this->flashMessage(trans('courses.finder.clone_in.success'));
        } catch (CloneException $e) {
            $this->flashMessage($e->getMessage(), 'text-bg-danger');
        }
    }

    /**
     * Move all items in the $keys array ($keys is 'card-1', 'folder-2', etc.).
     * Reload the page if $reloadAfterSave is true (useful for update the
     * breadcrumbs if the moved folder is the current displayed page).
     */
    public function moveIn(
        array $keys,
        ?int $dest = null,
        bool $reloadAfterSave = false,
    ): void {
        $this->authorize('massActionsForCardAndFolder', $this->course);

        $this->keysToEntities($keys)->each(
            fn ($entity) => MoveService::moveCardOrFolder(
                $entity,
                $dest ? Folder::findOrFail($dest) : null,
            ),
        );

        $this->dispatchToModals();

        $this->flashMessage(trans('courses.finder.move_in.success'));

        if ($reloadAfterSave) {
            $this->redirect(url()->previous());
        }
    }

    /**
     * Return a collection of Cards and Folders models corresponding to
     * the given keys' list.
     */
    private function keysToEntities(
        array $keys,
        bool $withoutDescendants = true,
    ): Collection {
        return collect($keys)
            ->map(function ($key) {
                [$type, $key] = explode('-', $key);

                return
                    $type === FinderItemType::Card
                    ? Card::findOrFail($key)
                    : Folder::findOrFail($key);
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

    /**
     * Initialize the filters and the search boxes to their default values.
     */
    private function reinitFilters(): void
    {
        $this->filterSearchBoxes = [
            'name' => self::DEFAULT_SEARCH_BOX_NAME,
            CardBox::Box2 => self::DEFAULT_SEARCH_BOX_2,
            CardBox::Box3 => self::DEFAULT_SEARCH_BOX_3,
            CardBox::Box4 => self::DEFAULT_SEARCH_BOX_4,
        ];

        $this->arrayFilters = [
            'tag' => [],
            'editor' => [],
            'state' => [],
            'search' => [],
        ];
        unset($this->filters);

        $this->addJsForFilters();

        // We set "noDefaults" to true because react components have "wire:ignore"
        // attribute that lead to defaults values not being updated when
        // filters are cleared. So we indicate to react select that it should
        // not use the defaults values.
        $this->js('window.MultiFilterSelect.create(true)');
    }

    private function addJsForFilters()
    {
        $jsonFilters = collect($this->filterSearchBoxes)->toJson();
        $this->js("window.MultiFilterSelect.checkedFilter = $jsonFilters");
    }

    /**
     * Display a flash message with the given message and bootstrap class.
     * The flash message will automatically hide after some time.
     */
    private function flashMessage(
        string $message,
        string $bsClass = 'text-bg-success',
    ): void {
        session()->flash('message', $message);
        session()->flash('bsClass', $bsClass);

        // Hide the toast after x milliseconds.
        $this->js(<<<'JS'
            if (window.flashTimer) {
                clearTimeout(window.flashTimer);
            }

           window.flashTimer = setTimeout(function(){
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

        return ! empty($validator->validated());
    }

    /**
     * Dispatch events to the modal components to
     * update their list of folders.
     */
    private function dispatchToModals()
    {
        $this
            ->dispatch('item-created')
            ->to(ModalCreateCard::class);
        $this
            ->dispatch('item-created')
            ->to(ModalCreateFolder::class);
    }
}
