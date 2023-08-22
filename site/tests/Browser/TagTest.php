<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class TagTest extends DuskTestCase
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
     * Test the creation of a tag from course.
     */
    public function testCrudTagFromCourse(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/courses/1/configure/tags');

            // Create
            $tagName = 'a_new_tag';
            $browser->press('Ajouter une étiquette')
                ->waitForText('Annuler')
                ->type('#createTagModal [name="name"]', $tagName)
                ->press('#createTagModal [type="submit"]')
                ->waitForText('Étiquette créée.')
                ->assertPathIs('/courses/1/configure/tags')
                ->assertSee($tagName);

            // Update
            $newTagName = 'aaa_updated_tag_name';
            $browser->press("[data-bs-name='$tagName']")
                ->waitForText('Modifier')
                ->type('#editTagModal [name="name"]', $newTagName)
                ->press('Modifier')
                ->waitForText('Étiquette renommée.')
                ->assertPathIs('/courses/1/configure/tags')
                ->assertSee($newTagName);

            // Delete
            $browser->press('[data-bs-original-title="Supprimer l\'étiquette"]')
                ->waitForDialog()
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Étiquette supprimée.')
                ->assertPathIs('/courses/1/configure/tags')
                ->assertDontSee($newTagName);
        });
    }

    public function testCloneTag(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/courses/1/configure/tags');

            $browser->click('#rct-single-course-select')
                ->waitForText('Second space')
                ->screenshot('dsds')
                // Select the "Second space" option of react select tags.
                ->click('#rct-single-course-select [id$=listbox] > div > div:first-child')
                ->press('Reprendre')
                ->waitForText('Étiquettes reprises.')
                ->assertPathIs('/courses/1/configure/tags')
                ->assertSee('Test_tag_second_course')
                ->click('#rct-single-course-select')
                ->waitForText('Second space')
                // Select the "Second space" option of react select tags.
                ->click('#rct-single-course-select [id$=listbox] > div > div:first-child')
                ->press('Reprendre')
                ->waitForText('Toutes les étiquettes existent déjà dans cet espace.');
        });
    }

    public function testTagFromCard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/cards/4/edit');

            $newTag = 'a_new_tag';

            $browser->type('#rct-multi-tag-select input', $newTag)
                ->waitForText("Créer \"$newTag\"")
                // Select the "create" option of react select tags.
                ->click('#rct-multi-tag-select [id$=listbox] > div > div:last-child')
                ->waitForText('No options')
                ->waitForText($newTag)
                ->click("[aria-label='Remove $newTag']")
                ->waitUntilMissingText('No options')
                ->assertSee($newTag);
        });
    }
}
