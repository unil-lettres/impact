<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MoodleService
{
    public string $url;

    public string $token;

    public function __construct()
    {
        $this->url = config('const.moodle.url');
        $this->token = config('const.moodle.token');
    }

    /**
     * Retrieve Moodle course data by id.
     */
    public function getCourse(int $courseId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::get($this->url, [
            'wstoken' => $this->token,
            'wsfunction' => 'local_impactsync_get_courses',
            'courseids' => $courseId,
            'moodlewsrestformat' => 'json',
        ]);

        if (! $this->isResponseValid($response)) {
            return null;
        }

        return $response->collect()
            ->first();
    }

    /**
     * Retrieve Moodle users data by course id.
     */
    public function getUsers(int $courseId): ?Collection
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::get($this->url, [
            'wstoken' => $this->token,
            'wsfunction' => 'local_impactsync_course_get_enrolled_users',
            'courseid' => $courseId,
            'moodlewsrestformat' => 'json',
        ]);

        if (! $this->isResponseValid($response)) {
            return null;
        }

        return $response->collect();
    }

    /**
     * Check if the url & token needed to use the service are available.
     */
    public static function isConfigured(): bool
    {
        return config('const.moodle.url') && config('const.moodle.token');
    }

    /**
     * Check if the response is valid & has data.
     */
    private function isResponseValid(Response $response): bool
    {
        if ($response->failed() || $response->collect()->isEmpty()) {
            return false;
        }

        return true;
    }
}
