<?php

namespace App\Livewire;

use App\Card;
use App\Folder;
use App\Course;
use App\Enums\FinderRowType;
use App\Helpers\Helpers;
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

    #[Computed]
    public function rows()
    {
        return Helpers::getFolderContent(
            Course::find($this->course->id),
            null,
            $this->sortColumn,
            $this->sortDirection,
        );
    }

    #[Computed]
    public function lockedMove()
    {
        return (FALSE
            || $this->sortColumn != self::DEFAULT_SORT_COLUMN
            || $this->sortDirection != self::DEFAULT_SORT_DIRECTION
        );
    }


    #[Computed]
    public function sortAttributes($column)
    {
        if ($column === $this->sortColumn) {
            [$directionCss, $direction, $column] = match($this->sortDirection) {
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

    #[On('sort-updated')]
    public function move(int $id, string $type, int $position)
    {
        // TODO valdier les inputs

        if ($this->lockedMove())
            return;

        $entity = $type === FinderRowType::Folder ? Folder::find($id) : Card::Find($id);
        $entity->position = $position;
        $entity->save();
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
}
