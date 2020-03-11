<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

class CardTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    /**
     * Test list user cards.
     *
     * @return void
     * @throws Throwable
     */
    public function testListUserCards()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned');
        });
    }

    /**
     * Test view card as an editor.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCardAsEditor()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('student-user@example.com', 'password');

            $browser->assertSee('Second space')
                ->clickLink('Second space');

            $browser->assertSee('Test card second space')
                ->assertDontSee('Test card second space not assigned')
                ->clickLink('Test card second space');

            $browser->assertSee('Test card second space')
                ->assertDontSee('Configuration de la fiche');
        });
    }

    /**
     * Test view card as a teacher.
     *
     * @return void
     * @throws Throwable
     */
    public function testViewCardAsTeacher()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login())
                ->loginAsUser('teacher-user@example.com', 'password');

            $browser->assertSee('First space')
                ->clickLink('First space');

            $browser->assertSee('Test card first space')
                ->assertDontSee('Test card second space')
                ->clickLink('Test card first space');

            $browser->assertSee('Test card first space')
                ->assertSee('Configuration de la fiche');
        });
    }
}
