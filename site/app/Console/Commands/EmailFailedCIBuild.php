<?php

namespace App\Console\Commands;

use App\Mail\DuskFailure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailFailedCIBuild extends Command
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
     */
    public function handle(): void
    {
        Mail::to('mailtrap@test.com')
            ->send(new DuskFailure());
    }
}
