<?php

namespace Tests\Browser\Pages;

use App\Folder as AppFolder;

class Folder extends Page
{
    private AppFolder $folder;

    public function __construct(string $folderName){
        $this->folder = AppFolder::where('title', $folderName)->first();
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return "/folders/{$this->folder->id}";
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
