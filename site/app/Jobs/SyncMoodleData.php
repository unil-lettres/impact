<?php

namespace App\Jobs;

use App\Course;
use App\Enums\UserType;
use App\Services\MoodleService;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        $externalIds = $this->courses
            ->pluck('external_id')
            ->toArray();

        // Get available Moodle courses
        $availableCourses = (new MoodleService)
            ->getCourses($externalIds);

        if ($availableCourses) {
            // List the Impact external courses that are
            // not in the Moodle database anymore
            $orphans = collect($externalIds)->diff(
                $availableCourses
                    ->pluck('id')
                    ->filter()
            );

            // If any, log and update the orphan courses
            if ($orphans->isNotEmpty()) {
                Log::notice('Cannot sync the courses with the following external ids : '.$orphans->implode(', '));

                Course::whereIn('external_id', $orphans)
                    ->update(['orphan' => true]);
            }

            // Update non-orphan courses
            $nonOrphans = $availableCourses
                ->pluck('id')
                ->filter()
                ->all();
            Course::whereIn('external_id', $nonOrphans)
                ->update(['orphan' => false]);

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
                $impactCourse = $this->courses
                    ->where('external_id', $course['id'])
                    ->first();

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
                $allEnrollments = $impactCourse
                    ->enrollments()
                    ->withoutGlobalScopes()
                    ->with('user')
                    ->get();

                // Get emails from Moodle users
                $moodleEmails = $moodleUsers
                    ->pluck('email')
                    ->filter()
                    ->map(function ($email) {
                        return strtolower($email);
                    });

                // Find enrollments where the user's email is not in Moodle
                $orphanEnrollments = $allEnrollments
                    ->filter(function ($enrollment) use ($moodleEmails) {
                        return $enrollment->user && ! $moodleEmails->contains(strtolower($enrollment->user->email));
                    });

                $orphanImpactUserIds = $orphanEnrollments
                    ->pluck('user_id')
                    ->unique();

                if ($orphanImpactUserIds->isNotEmpty()) {
                    // If any, log the orphan Impact enrollments
                    Log::notice('Cannot sync the enrollments for course '.$impactCourse->id.
                        ' with the following user ids : '.$orphanImpactUserIds->implode(', '));

                    // Delete the orphan Impact enrollments
                    $orphanEnrollments->each(function ($enrollment) {
                        $enrollment->forceDelete();
                    });
                }
            }
        });
    }

    /**
     * The job failed to process.
     */
    public function failed(Throwable $exception): void
    {
        Log::error($exception->getMessage());
    }
}
