<?php

namespace App\Console\Commands;

use App\Jobs\SyncMoodleData;
use App\Services\MoodleService;
use Illuminate\Console\Command;

class MoodleSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Moodle courses and enrollments with the application';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (MoodleService::isConfigured()) {
            // Dispatch for async processing
            SyncMoodleData::dispatch();
        }
    }
}
