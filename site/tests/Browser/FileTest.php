<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Course;
use Tests\Browser\Pages\Login;
use Tests\DuskTestCase;
use Throwable;

class FileTest extends DuskTestCase
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
     * Test list files as an admin.
     *
     * @throws Throwable
     */
    public function test_list_files_as_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->waitFor('#files table tbody')
                ->assertSee('Test video file')
                ->assertSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }

    /**
     * Test list files as a manager.
     *
     * @throws Throwable
     */
    public function test_list_files_as_manager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->on(new Course('Second space'))
                ->filesIndex();

            $browser->waitFor('#files table tbody')
                ->assertSee('Test video file')
                ->assertDontSee('Test audio file')
                ->assertSee('Failed file')
                ->assertSee('Used file')
                ->assertDontSee('Deactivated file');
        });
    }

    /**
     * Test show linked card as a manager.
     *
     * @throws Throwable
     */
    public function test_show_linked_card_as_manager(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('manager-user@example.com', 'password');

            $browser->on(new Course('Second space'))
                ->filesIndex();

            $browser->waitFor('#files table tbody tr.ready.used span.base-popover');
            $browser->with('#files table tbody tr.ready.used', function ($used) {
                $used->click('span.base-popover');
            });
            $browser->waitForText('Test card with file')
                ->assertSee('Test card with file')
                ->clickLink('Test card with file')
                ->assertSee('Test card with file');
        });
    }

    /**
     * Test can play a file with the ready status.
     *
     * @throws Throwable
     */
    public function test_can_play_ready_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $fileId = \App\File::where('name', 'Test video file')
                ->value('id');
            $browser->visit("/admin/files/{$fileId}/edit")
                ->waitForText('Test video file')
                ->assertInputValue('status', 'ready')
                ->assertSourceHas('Url du fichier');
        });
    }

    /**
     * Test cannot play a file with the failed status.
     *
     * @throws Throwable
     */
    public function test_cannot_play_failed_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $fileId = \App\File::where('name', 'Failed file')
                ->value('id');
            $browser->visit("/admin/files/{$fileId}/edit")
                ->waitForText('Failed file')
                ->assertInputValue('status', 'failed')
                ->assertSourceMissing('Url du fichier');
        });
    }

    /**
     * Test cannot play a file with the transcoding status.
     *
     * @throws Throwable
     */
    public function test_cannot_play_transcoding_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $fileId = \App\File::where('name', 'Test audio file')
                ->value('id');
            $browser->visit("/admin/files/{$fileId}/edit")
                ->waitForText('Test audio file')
                ->assertInputValue('status', 'transcoding')
                ->assertSourceMissing('Url du fichier');
        });
    }

    /**
     * Test cannot edit the course of a used file.
     *
     * @throws Throwable
     */
    public function test_cannot_edit_course_of_used_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $fileId = \App\File::where('name', 'Used file')
                ->value('id');
            $browser->visit("/admin/files/{$fileId}/edit")
                ->waitForText('Test card with file')
                ->assertSee('Test card with file')
                ->click('#rct-single-course-select')
                ->assertDontSee('First space');
        });
    }

    /**
     * Test can delete an unused file.
     *
     * @throws Throwable
     */
    public function test_can_delete_unused_file(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new Login)
                ->loginAsUser('admin-user@example.com', 'password');

            $browser->visit('/admin/files');

            $browser->waitFor('#files table tbody tr.unused form.with-delete-confirm button');
            $this->stubConfirmAndClick(
                $browser,
                '#files table tbody tr.unused form.with-delete-confirm button'
            );
            $browser->waitForText('Fichier supprimé.')
                ->assertSee('Fichier supprimé.');
        });
    }
}
