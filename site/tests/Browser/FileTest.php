<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class FileTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test view folders.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewFilesAsAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Admin');

            $browser->clickLink('Fichiers');

            $browser->assertSee('Test video file')
                ->assertSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Transcoding file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }
}
