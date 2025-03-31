<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoodleService
{
    public ?string $apiUrl = null;

    public ?string $moodleUrl = null;

    public ?string $token = null;

    private string $format = 'json';

    public function __construct()
    {
        if (config('const.moodle.base') && config('const.moodle.api')) {
            $this->apiUrl = config('const.moodle.base').config('const.moodle.api');
        }

        if (config('const.moodle.base') && config('const.moodle.course')) {
            $this->moodleUrl = config('const.moodle.base').config('const.moodle.course');
        }

        $this->token = config('const.moodle.token');
    }

    /**
     * Retrieve the data of a Moodle course by id.
     */
    public function getCourse(int $courseId, bool $withUsers = true): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::get($this->apiUrl, [
            'wstoken' => $this->token,
            'wsfunction' => 'local_impactsync_get_courses',
            'courseids' => $courseId,
            'courseusers' => $withUsers ? 1 : 0,
            'moodlewsrestformat' => $this->format,
        ]);

        if (! $this->isResponseValid($response)) {
            return null;
        }

        return $withUsers ?
            $this->processUsers($response->collect())->first() : $response->collect()->first();
    }

    /**
     * Return the Moodle URL for a given Moodle ID.
     */
    public function getMoodleUrl(int $moodleID): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        return $this->moodleUrl.(string) $moodleID;
    }

    /**
     * Retrieve the data of multiple Moodle courses by id.
     */
    public function getCourses(array $courseIds, bool $withUsers = true): ?Collection
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::get($this->apiUrl, [
            'wstoken' => $this->token,
            'wsfunction' => 'local_impactsync_get_courses',
            'courseids' => Arr::join($courseIds, ','),
            'courseusers' => $withUsers ? 1 : 0,
            'moodlewsrestformat' => $this->format,
        ]);

        if (! $this->isResponseValid($response)) {
            return null;
        }

        return $withUsers ?
            $this->processUsers($response->collect()) : $response->collect();
    }

    /**
     * Check if the url & token needed to use the service are available.
     */
    public static function isConfigured(): bool
    {
        return config('const.moodle.base') && config('const.moodle.api') && config('const.moodle.token');
    }

    /**
     * Check if the response is valid & has data.
     */
    private function isResponseValid(Response $response): bool
    {
        if ($response->failed() || $response->collect()->isEmpty()) {
            return false;
        }

        // Check if the 'errorcode' exists in the response body
        $responseData = $response->json();
        if (isset($responseData['errorcode'])) {
            // If 'errorcode' is present, consider the response invalid
            Log::warning('Moodle API returned an error : '.$responseData['errorcode']);

            return false;
        }

        return true;
    }

    /**
     * Process the users of the courses to match the application
     * roles & remove duplicates.
     */
    private function processUsers(Collection $courses): Collection
    {
        return $courses->map(function ($course) {
            if (isset($course['users'])) {
                // Remove duplicate users (keep only the teacher if the user is both teacher & student)
                $users = collect($course['users']);
                $teachers = $users->where('role', 'teacher');
                $students = $users->where('role', 'student')
                    ->reject(fn ($student) => $teachers->contains('email', $student['email']));
                $course['users'] = $students->concat($teachers)->all();

                // Match the Moodle roles with the Impact roles
                foreach ($course['users'] as &$user) {
                    $user['role'] = match ($user['role']) {
                        'teacher' => 'manager',
                        'student' => 'member',
                        default => $user['role'],
                    };
                }
            }

            return $course;
        });
    }
}
