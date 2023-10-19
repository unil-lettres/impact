<?php

namespace Tests\Browser;

use App\Card;
use App\Course;
use App\Enums\FinderItemType;
use App\Livewire\ModalCreate;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Livewire\Livewire;
use Tests\Browser\Pages\Course as PagesCourse;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class CourseTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list courses as simple user.
     *
     * @return void
     *
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
     *
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
     *
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
     *
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
     *
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
     *
     * @throws Throwable
     */
    public function testEditLocalCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr.local .actions span:nth-child(1) a')
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
     *
     * @throws Throwable
     */
    public function testEditExternalCourse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr.external .actions span:nth-child(1) a')
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
     *
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
     *
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

    /**
     * Test change the transcription type.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function testChangeTranscriptionType()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space')
                ->assertSee('Test card features')
                ->clickLink('Test card features');

            $browser->click('#edit-box2')
                ->assertSee('Annuler')
                ->assertSee('Sauver')
                ->assertSee('Effacer texte')
                ->assertPresent('#rct-transcription')
                ->assertNotPresent('#rct-editor-box2');

            $browser->assertSee('Second space')
                ->clickLink('Second space')
                ->clickLink('Configuration de l\'espace')
                ->assertSee('Type de transcription');

            $browser->select('type', 'text')
                ->press('Mettre à jour la configuration')
                ->assertSee('Configuration de l\'espace mise à jour.');

            $browser->assertSee('Second space')
                ->clickLink('Second space')
                ->assertSee('Test card features')
                ->clickLink('Test card features');

            $browser->click('#edit-box2')
                ->assertSee('Annuler')
                ->assertSee('Sauver')
                ->assertDontSee('Effacer texte')
                ->assertPresent('#rct-editor-box2')
                ->assertNotPresent('#rct-transcription');
        });
    }

    public function testFilters(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $page = new PagesCourse('First space');
            $page->createCard($browser, 'Test card without tag');

            $browser
                ->visit($page)
                ->waitForText('Test card without tag');

            $browser
                ->click('.rct-multi-filter-select[placeholder="Etiquettes"]')
                ->click('#react-select-2-option-0');

            $browser
                ->waitUntilMissingText('Test card without tag')
                ->press('Tout effacer')
                ->waitForText('Test card without tag');
        });
    }

    public function testMultiSelect(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new PagesCourse('Second space'))
                ->waitUntilLoaded()
                ->click('.finder-folder')
                ->press('Tout séléctionner');

            $browser->assertSee('11 élément(s) sélectionné(s) dont 8 fiche(s)');
        });
    }

    public function testClone(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new PagesCourse('Second space'))
                ->waitUntilLoaded()
                ->click('.finder-folder')
                ->click('@multi-menu')
                ->click('@multi-copy-option');

            $browser->waitForText('Test folder (copie)');
        });
    }

    public function testDelete(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new PagesCourse('Second space'))
                ->waitUntilLoaded()
                ->click('.finder-folder')
                ->click('@multi-menu')
                ->click('@multi-delete-option')
                ->acceptDialog();

            $browser->waitUntilMissingText('Test folder');
        });
    }
}
