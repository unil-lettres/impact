<?php

namespace App\Livewire\Finder;

use App\Course;
use App\Helpers\Helpers;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Finder extends Component
{
    public $course;

    #[Computed]
    public function rows()
    {
        return Helpers::getFolderContent(Course::find($this->course->id));
    }

    public function render()
    {
        return view('livewire.finder.finder');
    }
}
