<?php

namespace Tests\Browser\Pages;

use App\Folder as AppFolder;
use Laravel\Dusk\Browser;

class Folder extends Page
{
    private AppFolder $folder;

    public function __construct(private string $folderName){
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

    public function waitUntilLoaded(Browser $browser)
    {
        $browser->waitForText($this->folderName);
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
