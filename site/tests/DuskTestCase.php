<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $server = 'http://localhost:9515';

        // Specific setup for local docker environment
        if (env('DOCKER_RUNNING', false)) {
            // Change the remote web driver server
            $server = 'http://impact-selenium:4444/wd/hub';

            // Setup & seed the database
            Artisan::call('migrate:fresh --database=testing --seed');

            // Install the version of ChromeDriver that matches the detected version of Chrome
            Artisan::call('dusk:chrome-driver --detect');
        }

        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
            '--disable-smooth-scrolling',
        ]);

        return RemoteWebDriver::create(
            $server, DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
