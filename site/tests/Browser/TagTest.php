<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Card;
use Tests\Browser\Pages\Course;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class TagTest extends DuskTestCase
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
     * Test the creation of a tag from course.
     */
    public function test_crud_tag_from_course(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->on(new Course('First space'))
                ->tagsIndex();

            // Create
            $tagName = 'a_new_tag';
            $browser->press('Ajouter une étiquette')
                ->waitForText('Annuler')
                ->type('#createTagModal [name="name"]', $tagName)
                ->press('#createTagModal [type="submit"]')
                ->waitForText('Étiquette créée.')
                ->assertPathIs('/courses/1/configure/tags')
                ->waitForText($tagName)
                ->assertSee($tagName);

            // Update
            $newTagName = 'aaa_updated_tag_name';
            $browser->press("[data-bs-name='$tagName']")
                ->waitForText('Modifier')
                ->type('#editTagModal [name="name"]', $newTagName)
                ->press('Modifier')
                ->waitForText('Étiquette renommée.')
                ->assertPathIs('/courses/1/configure/tags')
                ->waitForText($newTagName)
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

    public function test_clone_tag(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->on(new Course('First space'))
                ->tagsIndex();

            $browser->click('#rct-single-course-select')
                ->waitForText('Second space')
                // Select the "Second space" option of react select tags.
                ->click('#rct-single-course-select div[role="listbox"] > div:first-child')
                ->press('Reprendre')
                ->waitForText('Étiquettes reprises.')
                ->assertPathIs('/courses/1/configure/tags')
                ->waitForText('Test_tag_second_course')
                ->assertSee('Test_tag_second_course')
                ->click('#rct-single-course-select')
                ->waitForText('Second space')
                // Select the "Second space" option of react select tags.
                ->click('#rct-single-course-select div[role="listbox"] > div:first-child')
                ->press('Reprendre')
                ->waitForText('Toutes les étiquettes existent déjà dans cet espace.');
        });
    }

    public function test_tag_from_card(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->on(new Card('Test card first space'))
                ->edit();

            $newTag = 'a_new_tag';

            $browser->type('#rct-multi-tag-select input', $newTag)
                ->waitForText("Créer \"$newTag\"")
                // Select the "create" option of react select tags.
                ->click('#rct-multi-tag-select div[role="listbox"] > div:last-child')
                ->waitForText('No options')
                ->waitForText($newTag)
                ->click("[aria-label='Remove $newTag']")
                ->waitUntilMissingText('No options')
                ->assertSee($newTag);
        });
    }
}
