<?php

namespace Tests\Browser\Pages;

use App\Folder as AppFolder;
use Laravel\Dusk\Browser;

class Folder extends Page
{
    private AppFolder $folder;

    public function __construct(string $folderName)
    {
        $this->folder = AppFolder::where('title', $folderName)->first();
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return "/folders/{$this->folder->id}";
    }

    /**
     * Wait to the finder to be fully loaded.
     */
    public function waitUntilLoaded(Browser $browser): void
    {
        $browser->waitForText($this->folder->title);
    }

    /**
     * Return the id of the folder.
     */
    public function id(): int
    {
        return $this->folder->id;
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@element' => '#selector',
        ];
    }
}
