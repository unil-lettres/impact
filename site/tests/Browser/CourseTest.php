<?php

namespace Tests\Browser;

use App\State;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Card as PagesCard;
use Tests\Browser\Pages\Course as PagesCourse;
use Tests\Browser\Pages\Folder as PagesFolder;
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
     * @throws Throwable
     */
    public function testListCoursesAsUser(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('member-user@example.com', 'password');

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
     * @throws Throwable
     */
    public function testListCoursesAsAdmin(): void
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
     * @throws Throwable
     */
    public function testViewCourseAsTeacher(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->assertSee('First space')
                ->visit(new PagesCourse('First space'));

            $browser->assertSee('Configuration de l\'espace')
                ->assertSee('Créer une fiche');
        });
    }

    /**
     * Test view course as a member.
     *
     * @throws Throwable
     */
    public function testViewCourseAsMember(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('member-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->visit(new PagesCourse('Second space'));

            $browser->assertDontSee('Configuration de l\'espace')
                ->assertDontSee('Créer une fiche');
        });
    }

    /**
     * Test create a course.
     *
     * @throws Throwable
     */
    public function testCreateCourse(): void
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
     * @throws Throwable
     */
    public function testEditLocalCourse(): void
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
     * Test cannot edit an external course.
     *
     * @throws Throwable
     */
    public function testCannotEditExternalCourse(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            // Check that the edit link is not present for external courses
            $browser->assertNotPresent('#courses table tbody tr.external .actions span a[aria-label="Modifier l\'espace"]');
        });
    }

    /**
     * Test disable a course.
     *
     * @throws Throwable
     */
    public function testDisableCourse(): void
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
     * @throws Throwable
     */
    public function testDeleteCourse(): void
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
     * @throws Throwable
     */
    public function testChangeTranscriptionType(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit(new PagesCard('Test card features'));

            $browser->click('#edit-box2')
                ->assertSee('Annuler')
                ->assertSee('Sauver')
                ->assertSee('Effacer texte')
                ->assertPresent('#rct-transcription')
                ->assertNotPresent('#rct-editor-box2');

            $browser->visit(new PagesCourse('Second space'))
                ->clickLink('Configuration de l\'espace')
                ->assertSee('Type de transcription');

            $browser->select('type', 'text')
                ->press('Mettre à jour la configuration')
                ->assertSee('Configuration de l\'espace mise à jour.');

            $browser->visit(new PagesCard('Test card features'));

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
                ->waitUntilLoaded()
                ->click('.rct-multi-filter-select[placeholder="Etiquettes"]')
                ->click('#react-select-2-option-0');

            $browser
                ->waitUntilMissingText('Test card without tag')
                ->press('Tout effacer')
                ->waitForText('Test card without tag')
                ->assertSee('Test card without tag');
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

            $browser
                ->waitForText('Test folder (copie)')
                ->assertSee('Test folder (copie)');
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

            $browser
                ->waitUntilMissingText('Test folder')
                ->assertDontSee('Test folder');
        });
    }

    public function testMoveIn(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $pageCourse = new PagesCourse('Second space');
            $pageCard = new PagesCard('Test card second space not assigned');
            $pageFolder = new PagesFolder('Test folder');

            $browser
                ->visit($pageCourse)
                ->waitUntilLoaded()
                ->click("@finder-card-{$pageCard->id()}")
                ->click('@multi-menu')
                ->click('@multi-movein-option');

            $browser
                ->waitForText('Déplacer dans...')
                ->select('#modalMoveIn-name', $pageFolder->id())
                ->press('Déplacer')
                ->waitUntilMissingText('Test card second space not assigned')
                ->assertSee('Elément(s) déplacé(s) avec succès.');

            $browser
                ->visit($pageFolder)
                ->waitForText('Test card second space not assigned')
                ->assertSee('Test card second space not assigned');
        });
    }

    public function testCloneIn(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $pageCourse = new PagesCourse('Second space');
            $pageCourseDest = new PagesCourse('First space');
            $pageCard = new PagesCard('Test card second space not assigned');

            $browser
                ->visit($pageCourse)
                ->waitUntilLoaded()
                ->click("@finder-card-{$pageCard->id()}")
                ->click('@multi-menu')
                ->click('@multi-clonein-option');

            $browser
                ->waitForText('Dupliquer dans...')
                ->select('#modalCloneIn-name', $pageCourseDest->id())
                ->press('Dupliquer')
                ->waitForText('Elément(s) copiés avec succès dans l\'espace.')
                ->assertSee('Elément(s) copiés avec succès dans l\'espace.');

            $browser
                ->visit($pageCourseDest)
                ->waitForText('Test card second space not assigned')
                ->assertSee('Test card second space not assigned');
        });
    }

    public function testEditState(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $pageCourse = new PagesCourse('Second space');
            $pageCard = new PagesCard('Test card second space not assigned');
            $archivedState = State::where('name', 'archivé')->where('course_id', $pageCourse->id())->first();

            $browser
                ->visit($pageCourse)
                ->assertDontSee('archivé') // To be sure our futur assertion is correct (should not have any archived card).
                ->waitUntilLoaded()
                ->click("@finder-card-{$pageCard->id()}")
                ->click('@multi-menu')
                ->click('@multi-updatestate-option');

            $browser
                ->waitForText('Modifier l\'état...')
                ->select('#modalUpdateState select', $archivedState->id)
                ->press('Modifier')
                ->waitForText('État mis à jour.')
                ->assertSee('État mis à jour.')
                ->assertSee('archivé');
        });
    }
}
