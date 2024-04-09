<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Folder;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class BreadcrumbsTest extends DuskTestCase
{
    use ProvidesBrowser;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test view folders.
     *
     * @throws Throwable
     */
    public function testViewBreadcrumbs(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new Folder('Test grand child folder'))
                ->assertSee('Liste des espaces / Second space / Test folder / Test child folder');
        });
    }
}
