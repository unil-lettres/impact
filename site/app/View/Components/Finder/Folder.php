<?php

namespace App\View\Components\Finder;

use App\Folder as AppFolder;
use App\Services\FinderTree;
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
     * Whether the folder contains cards to print, ignoring the filters and the
     * permissions of the user.
     */
    public bool $hasCardsToPrint;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public AppFolder $folder,
        public FinderTree $tree,
        public string $modalCloneId,
        public string $modalMoveId,
        public int $depth = 0,
    ) {
        $this->items = $tree->items($folder);
        $this->countCards = $tree->countCardsRecursive($folder);
        $this->hasCardsToPrint = $tree->hasCardsRecursive($folder);
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
        $hasFolderUpdateRights = auth()->user()->can('update', $this->folder);

        return $this->countCards > 0 || ! $this->tree->hasFilters() && $hasFolderUpdateRights;
    }
}
