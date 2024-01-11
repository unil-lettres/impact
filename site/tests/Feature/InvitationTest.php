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
     * Test the invitation created email content.
     */
    public function testInvitationEmailContent(): void
    {
        $invitation = Invitation::factory()
            ->create();

        $mailable = new InvitationCreated($invitation);

        $mailable->assertSeeInHtml($invitation->creator->name);
        $mailable->assertSeeInHtml($invitation->getLink());
    }
}
