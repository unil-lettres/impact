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

            $browser->assertSee('Actions');
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

            $browser->assertDontSee('Actions');
        });
    }
}
