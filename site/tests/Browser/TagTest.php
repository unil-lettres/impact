<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;

class TagTest extends DuskTestCase
{
    use ProvidesBrowser;
    use WithFaker;

    protected static bool $migrated = false;

    public function setUp(): void
    {
        parent::setUp();

        if (! static::$migrated) {
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
     * Test the creation of a tag from course.
     */
    public function testCrudTagFromCourse(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/courses/1/configure');

            // Create
            $tagName = fake()->word();
            $browser->type('name', $tagName)
                ->press("Ajouter l'étiquette")
                ->waitForText('Étiquette créée.')
                ->assertPathIs('/courses/1/configure')
                ->assertSee($tagName);

            // Update
            $newTagName = fake()->word();
            $browser->press("[data-bs-name='$tagName']")
                ->waitForText("Renommer l'étiquette")
                ->type('.modal-dialog [name="name"]', $newTagName)
                ->press('Enregistrer')
                ->waitForText('Étiquette renommée.')
                ->assertPathIs('/courses/1/configure')
                ->assertSee($newTagName);

            // Delete
            $browser->press('[data-bs-original-title="Supprimer l\'étiquette"]')
                ->waitForDialog()
                ->assertDialogOpened('Êtes-vous sûr de vouloir supprimer cet élément ?')
                ->acceptDialog()
                ->waitForText('Étiquette supprimée.')
                ->assertPathIs('/courses/1/configure')
                ->assertDontSee($newTagName);
        });
    }

    public function testCloneTag(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/courses/1/configure');

            $browser->press('Reprendre les étiquettes')
                ->select('course_id', '2')
                ->waitUntilEnabled('#collapseCloneTags button[type="submit"]')
                ->press('#collapseCloneTags button[type="submit"]')
                ->waitForText('Étiquettes reprises.')
                ->assertPathIs('/courses/1/configure')
                ->assertSee('Test_tag_second_course')
                ->press('Reprendre les étiquettes')
                ->select('course_id', '2')
                ->waitUntilEnabled('#collapseCloneTags button[type="submit"]')
                ->press('#collapseCloneTags button[type="submit"]')
                ->waitForText('Toutes les étiquettes existent déjà dans cet espace.');
        });
    }

    public function testTagFromCard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/cards/4/edit');

            $newTag = fake()->word();

            // The selector "...select-2" is relative to the number of
            // react-select present in the page.
            $browser->type('#react-select-2-input', $newTag)
                ->waitForText("Créer \"$newTag\"")
                // The selector "...option-2" is relative to the number of tags
                // present in the list.
                ->click('#react-select-2-option-2')
                ->waitForText('No options')
                ->waitForText($newTag)
                ->click("[aria-label='Remove $newTag']")
                ->waitUntilMissingText('No options')
                ->assertSee($newTag);
        });
    }
}