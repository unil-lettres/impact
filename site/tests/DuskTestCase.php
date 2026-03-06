<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
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

        // Increase the default wait timeout for slow CI environments (default is 5s)
        Browser::$waitSeconds = 20;

        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless=new',
            '--no-sandbox',
            '--window-size=1920,1200',
            '--disable-smooth-scrolling',
            '--disable-popup-blocking',
            '--no-first-run',
        ]);

        return RemoteWebDriver::create(
            $server, DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Stub window.confirm to return true and click the given selector.
     *
     * Chrome 134+ headless does not support waitForDialog/assertDialogOpened/acceptDialog
     * reliably. This helper pre-stubs window.confirm so the form submits immediately
     * without a native dialog, avoiding race conditions with the WebDriver dialog API.
     */
    protected function stubConfirmAndClick(Browser $browser, string $selector): Browser
    {
        $jsonSelector = json_encode($selector);
        $browser->script(
            'window.confirm = function() { return true; };'.
            "document.querySelector({$jsonSelector}).click();"
        );

        return $browser;
    }
}
