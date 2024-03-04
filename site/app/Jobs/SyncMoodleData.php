<?php

namespace App\Jobs;

use App\Course;
use App\Enums\UserType;
use App\Services\MoodleService;
use App\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMoodleData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The collection of Impact courses to sync.
     */
    protected Collection $courses;

    /**
     * Number of seconds the job can run before timing out.
     */
    public int $timeout;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->courses = Course::whereNotNull('external_id')->get();

        $this->timeout = config('const.moodle.sync.timeout');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all the external ids of the Impact courses
        $externalIds = $this->courses->pluck('external_id')->toArray();

        // Get available Moodle courses
        $availableCourses = (new MoodleService())
            ->getCourses($externalIds);

        if ($availableCourses) {
            // List the Impact external courses that are
            // not in the Moodle database anymore.
            $orphans = collect($externalIds)->diff(
                $availableCourses->pluck('id')->filter()
            );

            // If any, log the orphan courses
            if ($orphans->isNotEmpty()) {
                Log::warning('Cannot sync the courses with the following external ids : '.$orphans->implode(', '));
            }

            $this->sync($availableCourses);
        }
    }

    /**
     * Sync the users & enrollments fot the available courses
     */
    private function sync(\Illuminate\Support\Collection $availableCourses): void
    {
        $availableCourses->each(function ($course) {
            if ($course['id']) {
                $moodleUsers = collect($course['users']) ?? collect();
                $impactCourse = $this->courses->where('external_id', $course['id'])->first();

                // Sync the Impact enrollments with the Moodle users
                foreach ($moodleUsers as $moodleUser) {
                    $email = $moodleUser['email'] ?? null;
                    $firstname = $moodleUser['firstname'] ?: '';
                    $lastname = $moodleUser['lastname'] ?: '';
                    $role = $moodleUser['role'] ?? null;

                    if ($email && $role) {
                        // Get or create the Impact user
                        $impactUser = User::firstOrCreate(
                            ['email' => $email],
                            ['name' => $firstname.' '.$lastname, 'type' => UserType::Aai]
                        );

                        // Update or create the Impact enrollment
                        $impactCourse->enrollments()->updateOrCreate(
                            ['user_id' => $impactUser->id],
                            ['role' => $role]
                        );
                    }
                }

                // Get the Impact user ids belonging to orphan enrollments
                $orphanImpactUserIds = User::whereIn(
                    'email',
                    $impactCourse->enrollments
                        ->pluck('user.email')
                        ->diff(
                            $moodleUsers->pluck('email')
                        )
                )->get()->pluck('id');

                if ($orphanImpactUserIds->isNotEmpty()) {
                    // If any, log the orphan Impact enrollments
                    Log::warning('Cannot sync the enrollments for course '.$impactCourse->id.
                        ' with the following user ids : '.$orphanImpactUserIds->implode(', '));

                    // Delete the orphan Impact enrollments
                    $impactCourse->enrollments->whereIn('user_id', $orphanImpactUserIds)
                        ->each(function ($enrollment) {
                            $enrollment->forceDelete();
                        });
                }
            }
        });
    }

    /**
     * The job failed to process.
     */
    public function failed(Exception $exception): void
    {
        Log::error($exception->getMessage());
    }
}
