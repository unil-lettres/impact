<?php

namespace App\Livewire\Finder;

use App\Folder as AppFolder;
use App\Helpers\Helpers;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Folder extends Component
{
    public AppFolder $folder;
    public $depth = 0;

    #[On('sort-updated')]
    public function move(int $position)
    {
        // TODO VALIDER position (authorize)
        $this->folder->position = $position;
        $this->folder->save();
    }

    #[Computed]
    public function rows()
    {
        return Helpers::getFolderContent($this->folder->course, $this->folder);
    }

    public function render()
    {
        return view('livewire.finder.folder');
    }
}
