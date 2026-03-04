<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
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
            '--window-size=1920,1080',
            '--disable-dev-shm-usage',
        ]);

        $driver = RemoteWebDriver::create(
            $server, DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );

        // Chrome v132+ in --headless=new mode silently dismisses native browser dialogs
        // (confirm, alert, prompt) before WebDriver can intercept them. We override
        // window.confirm to always return true so form submissions that rely on confirm()
        // proceed without a native dialog, making waitForDialog() unnecessary.
        $devTools = new ChromeDevToolsDriver($driver);
        $devTools->execute('Page.addScriptToEvaluateOnNewDocument', [
            'source' => 'window.confirm = () => true; window.alert = () => {}; window.prompt = (msg, def) => def ?? "";',
        ]);

        return $driver;
    }
}
