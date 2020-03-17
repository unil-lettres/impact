<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class CourseTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list courses as simple user.
     *
     * @return void
     * @throws Throwable
     */
    public function testListCoursesAsUser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Liste des espaces')
                ->assertPathIs('/');

            $browser->assertSee('Second space')
                ->assertDontSee('First space')
                ->assertDontSee('Deactivated space');
        });
    }

    /**
     * Test list courses as an admin user.
     *
     * @return void
     * @throws Throwable
     */
    public function testListCoursesAsAdmin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Liste des espaces')
                ->assertPathIs('/');

            $browser->assertSee('First space')
                ->assertSee('Second space')
                ->assertDontSee('Deactivated space');
        });
    }

    /**
     * Test view course as a teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCourseAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->assertSee('First space')
                ->clickLink('First space');

            $browser->assertSee('Configuration de l\'espace')
                ->assertSee('Créer une fiche');
        });
    }

    /**
     * Test view course as a student.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCourseAsStudent()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertDontSee('Configuration de l\'espace')
                ->assertDontSee('Créer une fiche');
        });
    }

    /**
     * Test create a course.
     *
     * @return void
     * @throws Throwable
     */
    public function testCreateCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->clickLink('Créer un espace');

            $browser->type('name', 'My new space')
                ->type('description', 'My new space description')
                ->press('Créer un espace')
                ->waitForText('Espace créé: My new space')
                ->assertSee('Espace créé: My new space')
                ->assertSee('My new space');
        });
    }

    /**
     * Test edit a local course.
     *
     * @return void
     * @throws Throwable
     */
    public function testEditLocalCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr.local:first-child .actions span:nth-child(1) a')
                ->assertDontSee('Identifiant Moodle')
                ->type('name', 'My new space updated')
                ->type('description', 'My new space description updated')
                ->press('Mettre à jour l\'espace')
                ->waitForText('Espace mis à jour.')
                ->assertSee('Espace mis à jour.')
                ->assertSee('My new space updated')
                ->assertInputValue('name', 'My new space updated')
                ->assertInputValue('description', 'My new space description updated');
        });
    }

    /**
     * Test edit an external course.
     *
     * @return void
     * @throws Throwable
     */
    public function testEditExternalCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr.external:first-child .actions span:nth-child(1) a')
                ->assertSee('Identifiant Moodle')
                ->type('name', 'External space updated')
                ->type('description', 'External space description updated')
                ->press('Mettre à jour l\'espace')
                ->waitForText('Espace mis à jour.')
                ->assertSee('Espace mis à jour.')
                ->assertSee('External space updated')
                ->assertInputValue('name', 'External space updated')
                ->assertInputValue('description', 'External space description updated');
        });
    }

    /**
     * Test disable a course.
     *
     * @return void
     * @throws Throwable
     */
    public function testDisableCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr:first-child .actions form.with-disable-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir désactiver cet élément ?')
                ->acceptDialog()
                ->waitForText('Espace désactivé.')
                ->assertSee('Espace désactivé.')
                ->assertSee('Désactivé')
                ->assertPathIs('/admin/courses');
        });
    }

    /**
     * Test delete a course.
     *
     * @return void
     * @throws Throwable
     */
    public function testDeleteCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr:first-child .actions form.with-delete-confirm button')
                ->waitForDialog($seconds = null)
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Espace supprimé.')
                ->assertSee('Espace supprimé.')
                ->assertPathIs('/admin/courses');
        });
    }
}
