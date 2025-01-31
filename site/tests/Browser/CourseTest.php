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
    public function test_list_courses_as_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->assertSee('Liste des espaces')
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

            $browser->assertSee('Deactivated space')
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
    public function test_view_course_as_member(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
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
    public function test_create_course(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
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
    public function test_edit_local_course(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
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
     * Test disable a course.
     *
     * @throws Throwable
     */
    public function test_disable_course(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses');

            $browser->click('#courses table tbody tr:nth-child(3) .actions form.with-disable-confirm button')
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
    public function test_delete_course(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
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
    public function test_change_transcription_type(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
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

    public function test_filters(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $page = new PagesCourse('First space');
            $page->createCard($browser, 'Test card without tag');

            $browser
                ->visit($page)
                ->waitUntilLoaded()
                ->click('.rct-multi-filter-select[placeholder="Etiquettes"]')
                ->click('#react-select-4-option-0');

            $browser
                ->waitUntilMissingText('Test card without tag')
                ->press('Tout effacer')
                ->waitForText('Test card without tag')
                ->assertSee('Test card without tag');
        });
    }

    public function test_multi_select(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser
                ->visit(new PagesCourse('Second space'))
                ->waitUntilLoaded()
                ->click('.finder-folder')
                ->press('Tout séléctionner');

            $browser->assertSee('11 élément(s) sélectionné(s) dont 8 fiche(s)');
        });
    }

    public function test_clone(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
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

    public function test_delete(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
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

    public function test_move_in(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
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

    public function test_clone_in(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
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

    public function test_edit_state(): void
    {
        $this->browse(function (Browser $browser) {

            $browser
                ->visit(new Login)
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
