<?php

namespace App\Console\Commands;

use App\Mail\DuskFailure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailFailedTravisCIBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:failure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send dusk screenshots by mail on tests failure';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Please add the CI_FAILURE_RECIPIENT environment variable to your CI tool,
        // or modify the MAIL_FROM_ADDRESS in your laravel .env file.
        return Mail::to(env('CI_FAILURE_RECIPIENT', env('MAIL_FROM_ADDRESS')))->send(new DuskFailure());
    }
}
