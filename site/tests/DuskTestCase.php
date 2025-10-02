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
        static::startChromeDriver(['--port=9515']);
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
            '--headless=old',
            '--window-size=1920,1080',
            '--disable-dev-shm-usage',
            '--disable-software-rasterizer',
            '--disable-features=DialMediaRouteController',
            '--disable-search-engine-choice-screen',
            '--disable-features=ImprovedKeyboardShortcuts',
            '--disable-blink-features=AutomationControlled',
            '--user-data-dir='.sys_get_temp_dir().'/chrome-profile-'.uniqid(),
        ]);

        return RemoteWebDriver::create(
            $server, DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
