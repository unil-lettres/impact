<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Course as PagesCourse;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class CourseTest extends DuskTestCase
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
     * Test list courses as simple user.
     *
     * @throws Throwable
     */
    public function test_list_courses_as_user(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('member-user@example.com', 'password');

            $browser->waitForText('Liste des espaces')
                ->assertSee('Liste des espaces')
                ->assertPathIs('/');

            $browser->assertSee('Second space')
                ->assertDontSee('First space')
                ->assertDontSee('Deactivated space');
        });
    }

    /**
     * Test list courses as an admin user.
     *
     * @throws Throwable
     */
    public function test_list_courses_as_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->waitForText('Liste des espaces')
                ->assertSee('Liste des espaces')
                ->assertPathIs('/');

            $browser->assertSee('First space')
                ->assertSee('Second space')
                ->assertSee('Deactivated space');
        });
    }

    public function test_link_deactivated_courses_to_admin_view(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->waitForText('Deactivated space')
                ->assertSee('Deactivated space')
                ->clickLink('Deactivated space')
                ->assertSee('Gestion des espaces')
                ->assertPathIs('/admin/courses');
        });
    }

    /**
     * Test view course as a manager.
     *
     * @throws Throwable
     */
    public function test_view_course_as_manager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->waitForText('First space')
                ->assertSee('First space')
                ->visit(new PagesCourse('First space'));

            $browser->waitForText('Créer une fiche')
                ->assertSee('Configuration de l\'espace')
                ->assertSee('Créer une fiche');
        });
    }

    /**
     * Test view course as a member.
     *
     * @throws Throwable
     */
    public function test_view_course_as_member(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('member-user@example.com', 'password');

            $browser->waitForText('Second space')
                ->assertSee('Second space')
                ->visit(new PagesCourse('Second space'));

            $browser->waitForText('Tout ouvrir')
                ->assertDontSee('Configuration de l\'espace')
                ->assertDontSee('Créer une fiche');
        });
    }
}
