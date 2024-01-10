<?php

namespace App\View\Components\Finder;

use App\Folder as AppFolder;
use App\Helpers\Helpers;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Folder extends Component
{
    /**
     * Direct item in the folder.
     */
    public Collection $items;

    /**
     * Number of cards in the folder.
     */
    public int $countCards;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public AppFolder $folder,
        public Collection $filters,
        public array $filterSearchBoxes,
        public string $modalCloneId,
        public string $modalMoveId,
        public string $sortColumn = 'position',
        public string $sortDirection = 'asc',
        public int $depth = 0,
        public bool $lockedMove = false,
    ) {
        $this->items = Helpers::getFolderItems(
            $folder->course,
            $filters,
            $filterSearchBoxes,
            $folder,
            $sortColumn,
            $sortDirection,
        );

        $this->countCards = Helpers::numberOfItemsInFolder(
            $folder,
            $filters,
            $filterSearchBoxes,
            $sortColumn,
            $sortDirection,
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.finder.folder');
    }

    /**
     * If the component should render. It should not if the folder is empty and
     * the user cannot edit folders in the course or if the folder is empty due
     * to filters (all cards inside the folder are filtered).
     */
    public function shouldRender()
    {
        $hasFilters = $this->filters->some(fn ($filter) => $filter->isNotEmpty());

        $hasFolderUpdateRights = auth()->user()->can('update', $this->folder);

        return $this->countCards > 0 || ! $hasFilters && $hasFolderUpdateRights;
    }
}
