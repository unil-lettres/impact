<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class StateTest extends DuskTestCase
{
    use ProvidesBrowser;

    protected static bool $migrated = false;

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$migrated) {
            Artisan::call('migrate:fresh --seed');
            static::$migrated = true;
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test can view states management as teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testTeachersCanViewStatesManagement()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-teacher-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertSee('Configuration de l\'espace')
                ->clickLink('Configuration de l\'espace');

            $browser->assertSee('États')
                ->clickLink('États');

            $browser->assertSee('privé')
                ->assertSee('public')
                ->assertSee('privé')
                ->assertSee('archivé');
        });
    }

    /**
     * Test cannot view states management as student.
     *
     * @return void
     * @throws Throwable
     */
    public function testStudentsCannotViewStatesManagement()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('states-student-user@example.com', 'password');

            $browser->assertSee('Test states')
                ->clickLink('Test states');

            $browser->assertDontSee('Configuration de l\'espace');
        });
    }
}
