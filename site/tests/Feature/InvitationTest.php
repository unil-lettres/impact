<?php

namespace Tests\Feature;

use App\Invitation;
use App\Mail\InvitationCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test the local invitation created email content.
     */
    public function testLocalInvitationEmailContent(): void
    {
        $invitation = Invitation::factory()
            ->local()
            ->create();

        $mailable = new InvitationCreated($invitation);

        $mailable->assertSeeInHtml($invitation->creator->name);
        $mailable->assertSeeInHtml($invitation->course->name);
        $mailable->assertSeeInHtml($invitation->getLink());
    }

    /**
     * Test the local invitation link should not be null.
     */
    public function testLocalInvitationLinkIsNotNull(): void
    {
        $invitation = Invitation::factory()
            ->local()
            ->create();

        $this->assertNotNull($invitation->getLink());
    }

    /**
     * Test the aai invitation created email content.
     */
    public function testAaiInvitationEmailContent(): void
    {
        $invitation = Invitation::factory()
            ->aai()
            ->create();

        $mailable = new InvitationCreated($invitation);

        $mailable->assertSeeInHtml($invitation->creator->name);
        $mailable->assertSeeInHtml($invitation->course->name);
        $mailable->assertDontSeeInHtml(url('invitations/register'));
    }

    /**
     * Test the aai invitation link should be null.
     */
    public function testAaiInvitationLinkIsNull(): void
    {
        $invitation = Invitation::factory()
            ->aai()
            ->create();

        $this->assertNull($invitation->getLink());
    }
}
