<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class BreadcrumbsTest extends DuskTestCase
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
    public function testViewBreadcrumbs()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->clickLink('Second space')
                ->assertSee('Liste des espaces');

            $browser->clickLink('Test folder')
                ->assertSee('Liste des espaces / Second space');

            $browser->clickLink('Test card in folder')
                ->assertSee('Liste des espaces / Second space / Test folder');

            $browser->back();

            $browser->clickLink('Test child folder')
                ->assertSee('Liste des espaces / Second space / Test folder');

            $browser->press('Modifier le dossier')
                ->assertSee('Liste des espaces / Second space / Test folder / Test child folder');
        });
    }
}
