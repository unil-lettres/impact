<?php

namespace Tests\Browser;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\Browser\Pages\Localization;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;

class LocalizationTest extends DuskTestCase
{
    use ProvidesBrowser;

    public function tearDown(): void
    {
        parent::tearDown();
        static::closeAll();
    }

    // TODO: add functional tests
}
