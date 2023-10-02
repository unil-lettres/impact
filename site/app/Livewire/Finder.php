<?php

namespace App\Livewire;

use App\Card;
use App\Course;
use App\Enums\CardBox;
use App\Enums\FinderRowType;
use App\Folder;
use App\Helpers\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Finder extends Component
{
    const DEFAULT_SORT_COLUMN = 'position';

    const DEFAULT_SORT_DIRECTION = 'asc';

    public $course;

    public $sortColumn = self::DEFAULT_SORT_COLUMN;

    public $sortDirection = self::DEFAULT_SORT_DIRECTION;

    public $filters;

    public $filterCardDetails;

    public function mount()
    {
        $this->initFilters();
    }

    #[Computed]
    public function rows(): Collection
    {
        return Helpers::getFolderContent(
            Course::find($this->course->id),
            $this->filters,
            null,
            $this->sortColumn,
            $this->sortDirection,
        );
    }

    #[Computed]
    public function lockedMove(): bool
    {
        return false
            || $this->sortColumn != self::DEFAULT_SORT_COLUMN
            || $this->sortDirection != self::DEFAULT_SORT_DIRECTION;
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
        // TODO valdier les inputs

        if ($this->lockedMove()) {
            return;
        }

        $entity = $type === FinderRowType::Folder ? Folder::find($id) : Card::Find($id);
        $entity->position = $position;
        $entity->save();
    }

    #[On('add-element-to-filter')]
    public function addElementToFilter(mixed $filter, string $type)
    {
        // TODO valdier les inputs
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
        // TODO valdier les inputs
        $this->filters->put(
            $type,
            $this->filters->get($type)->filter(
                fn (mixed $_filter) => $_filter !== $filter,
            )->values(),
        );
    }

    public function sortAttributes($column): string
    {
        if ($column === $this->sortColumn) {
            [$directionCss, $direction, $column] = match ($this->sortDirection) {
                'asc' => ['desc', 'desc', $column],
                'desc' => ['remove', 'asc', 'position'],
            };
        } else {
            $direction = $directionCss = 'asc';
        }

        return <<<HTML
            class='d-flex cursor-pointer gap-2 sort-direction-$directionCss'
            wire:click='sort("$column", "$direction")'
        HTML;
    }

    public function toggleFilterCardDetail(string $detail)
    {
        if (! in_array($detail, ['name', CardBox::Box2, CardBox::Box3, CardBox::Box4])) {
            return;
        }

        if ($this->filterCardDetails->contains($detail)) {
            $this->filterCardDetails = $this->filterCardDetails->filter(
                fn (string $_detail) => $_detail !== $detail,
            );
        } else {
            $this->filterCardDetails->push($detail);
        }
    }

    public function clearFilters()
    {
        $this->initFilters();
    }

    public function sort($column, $direction): void
    {
        // TODO valdier les inputs
        $this->sortColumn = $column;
        $this->sortDirection = $direction;
    }

    public function cloneCard(Card $card): void
    {
        // TODO authorizations (and @can in the view)
        $card->clone();
    }

    public function cloneFolder(Folder $folder): void
    {
        // TODO authorizations (and @can in the view)
        $folder->clone();
    }

    public function cloneMultiple(array $keys): void
    {
        // TODO authorizations (and @can in the view)
        // TODO valdier les inputs
        // TODO doit être teacher du course des keys
        // TODO toutes les keys doivent provenir du même course
        $this->keysToEntities($keys)->each(fn ($entity) => $entity->clone());
    }

    public function renameFolder(Folder $folder, string $newName)
    {
        // TODO authorizations (and @can in the view)
        $validator = Validator::make(['newName' => $newName], [
            'newName' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            $this->flashMessage($validator->errors()->first(), 'text-bg-danger');

            return;
        }

        $validated = $validator->validated();

        $folder->title = $validated['newName'];
        $folder->save();
    }

    public function render()
    {
        return view('livewire.finder');
    }

    public function destroyFolder(Folder $folder)
    {
        $this->authorize('forceDelete', $folder);

        $folder->forceDelete();
    }

    public function destroyCard(Card $card)
    {
        $this->authorize('forceDelete', $card);

        $card->forceDelete();
    }

    public function destroyMultiple(array $keys): void
    {
        // TODO authorizations (and @can in the view)
        // TODO valdier les inputs
        // TODO doit être teacher du course des keys
        // TODO toutes les keys doivent provenir du même course
        $this->keysToEntities($keys)->each(
            fn ($entity) => $entity->forceDelete(),
        );
        $this->js('selectedItems = []');
    }

    public function cloneIn(array $keys, Course $dest)
    {
        // TODO authorizations (and @can in the view)
        // TODO valdier les inputs
        // TODO doit être teacher du course des keys
        // TODO toutes les keys doivent provenir du même course
        $this->keysToEntities($keys)->each(
            fn ($entity) => $entity->clone(null, $dest),
        );

        $this->flashMessage(trans('courses.finder.menu.clone_in.success'));
    }

    public function moveIn(array $keys, int $dest = null)
    {
        // TODO authorizations (and @can in the view)
        // TODO valdier les inputs
        // TODO doit être teacher du course des keys
        // TODO toutes les keys doivent provenir du même course
        $this->keysToEntities($keys)->each(
            fn ($entity) => $entity->move($dest ? Folder::find($dest) : null),
        );

        $this->flashMessage(trans('courses.finder.menu.move_in.success'));
    }

    private function keysToEntities(array $keys, $withoutDescendants = true): Collection
    {
        return collect($keys)
            ->map(function ($key) {
                [$type, $key] = explode('-', $key);

                return $type === FinderRowType::Card ? Card::find($key) : Folder::find($key);
            })
            ->filter(function ($entity) use ($keys, $withoutDescendants) {
                return $withoutDescendants && $entity->getAncestors(false)->pluck('id')->map(
                    fn ($id) => FinderRowType::Folder."-$id",
                )->intersect($keys)->isEmpty();
            });

    }

    private function initFilters()
    {
        // TODO check what is the default checked state on legacy
        $this->filterCardDetails = collect(['name', 'box2', 'box3', 'box4']);
        $this->filters = collect([
            'tag' => collect([]),
            'editor' => collect([]),
            'state' => collect([]),
            'card' => collect([]),
        ]);

        // Need to update checked state with Javascript because of how checked
        // attribute works (updating the HTML with the attribute don't show
        // the checked state visually).
        collect([
            'filterCardName',
            'filterCardCase2',
            'filterCardCase3',
            'filterCardCase4',
        ])->each(function ($filter) {
            $this->js("document.getElementById('$filter').checked = true;");
        });
    }

    private function flashMessage(string $message, string $bsClass = 'text-bg-success')
    {
        session()->flash('message', $message);
        session()->flash('bsClass', $bsClass);
        $this->js(<<<'JS'
           setTimeout(function(){
                document.getElementById('toast-flash').classList.remove('show');
            }, 5000);
        JS);
    }
}
