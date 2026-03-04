<?php

namespace Tests\Browser;

use App\Course as AppCourse;
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

            $browser->waitForText('Second space')
                ->assertSee('Second space')
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

            $browser->visit('/admin/courses')
                ->waitFor('@course-create-link')
                ->click('@course-create-link')
                ->waitFor('[name="name"]')
                ->type('name', 'My new space')
                ->type('description', 'My new space description')
                ->scrollTo('@course-create-submit')
                ->click('@course-create-submit')
                ->waitForLocation('/admin/courses')
                ->assertPathIs('/admin/courses')
                ->assertSee('My new space');

            $this->assertTrue(AppCourse::where('name', 'My new space')->exists());
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
            $course = AppCourse::where('name', 'First space')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses')
                ->waitFor("@course-edit-{$course->id}")
                ->click("@course-edit-{$course->id}")
                ->waitFor('#edit-course [name="name"]')
                ->type('name', 'First space updated')
                ->type('description', 'First space description updated')
                ->scrollTo('@course-update-submit')
                ->click('@course-update-submit')
                ->waitForLocation('/admin/courses')
                ->assertPathIs('/admin/courses');

            $course->refresh();
            $this->assertSame('First space updated', $course->name);
            $this->assertSame('First space description updated', $course->description);
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
            $course = AppCourse::where('name', 'Second space')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses')
                ->waitFor("@course-disable-{$course->id}")
                ->click("@course-disable-{$course->id}")
                ->waitForLocation('/admin/courses')
                ->assertPathIs('/admin/courses');

            $disabledCourse = AppCourse::withTrashed()->where('id', $course->id)->first();
            $this->assertNotNull($disabledCourse->deleted_at);
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
            $course = AppCourse::withTrashed()->where('name', 'Deactivated space')->first();

            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/courses')
                ->waitFor("@course-delete-{$course->id}")
                ->click("@course-delete-{$course->id}")
                ->waitForLocation('/admin/courses')
                ->assertPathIs('/admin/courses');

            $this->assertTrue(AppCourse::withTrashed()->where('id', $course->id)->doesntExist());
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

            $pageCourse = new PagesCourse('Second space');

            $browser->visit("/courses/{$pageCourse->id()}/configure")
                ->waitFor('select#type')
                ->select('type', 'text')
                ->click('@course-config-update-submit')
                ->waitForLocation("/courses/{$pageCourse->id()}/configure")
                ->assertPathIs("/courses/{$pageCourse->id()}/configure");

            $browser->visit(new PagesCard('Test card features'))
                ->waitFor('#edit-box2')
                ->click('#edit-box2')
                ->waitFor('#rct-editor-box2')
                ->assertPresent('#rct-editor-box2');
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
                ->assertPresent('.rct-multi-filter-select')
                ->assertSee('Test card without tag');
        });
    }

    public function test_multi_select(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $pageCourse = new PagesCourse('Second space');
            $pageFolder = new PagesFolder('Test folder');

            $browser
                ->visit($pageCourse)
                ->waitUntilLoaded()
                ->click("@finder-folder-{$pageFolder->id()}")
                ->waitFor('@multi-menu')
                ->click('@multi-menu')
                ->assertPresent('@multi-copy-option')
                ->assertPresent('@multi-delete-option');
        });
    }

    public function test_clone(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $pageCourse = new PagesCourse('Second space');
            $pageFolder = new PagesFolder('Test folder');

            $browser
                ->visit($pageCourse)
                ->waitUntilLoaded()
                ->click("@finder-folder-{$pageFolder->id()}")
                ->waitFor('@multi-menu')
                ->click('@multi-menu')
                ->click('@multi-copy-option')
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

            $pageCourse = new PagesCourse('Second space');
            $pageFolder = new PagesFolder('Test folder');

            $browser
                ->visit($pageCourse)
                ->waitUntilLoaded()
                ->click("@finder-folder-{$pageFolder->id()}")
                ->waitFor('@multi-menu')
                ->click('@multi-menu')
                ->click('@multi-delete-option')
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
                ->waitFor('@multi-menu')
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
                ->waitFor('@multi-menu')
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
                ->waitFor('@multi-menu')
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
