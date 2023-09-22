<?php

namespace App\Livewire;

use App\Card;
use App\Course;
use App\Enums\FinderRowType;
use App\Folder;
use App\Helpers\Helpers;
use Illuminate\Support\Collection;
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

    public function clearFilters()
    {
        $this->initFilters();
    }

    public function sort($column, $direction)
    {
        $this->sortColumn = $column;
        $this->sortDirection = $direction;
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

    private function initFilters()
    {
        $this->filters = collect([
            'tag' => collect([]),
            'editor' => collect([]),
            'state' => collect([]),
            'name' => collect([]),
        ]);
    }
}
