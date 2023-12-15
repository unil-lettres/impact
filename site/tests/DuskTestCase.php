<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     *
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $server = 'http://localhost:9515';

        if (! env('CI')) {
            // Change the remote web driver server for docker environment
            $server = 'http://impact-selenium:4444/wd/hub';

            // Setup & seed the database for docker environment
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
