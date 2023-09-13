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
    public $course;

    #[Computed]
    public function rows()
    {
        return Helpers::getFolderContent(Course::find($this->course->id));
    }

    #[On('sort-updated')]
    public function move(int $id, string $type, int $position)
    {
        // TODO valdier les inputs
        $entity = $type === FinderRowType::Folder ? Folder::find($id) : Card::Find($id);
        $entity->position = $position;
        $entity->save();
    }

    public function render()
    {
        return view('livewire.finder');
    }
}
