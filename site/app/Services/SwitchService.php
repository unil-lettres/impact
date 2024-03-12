<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class SwitchService
{
    public string $user;

    public string $password;

    public string $endpoint;

    public string $api_version;

    public function __construct()
    {
        $this->user = config('const.switch.user');
        $this->password = config('const.switch.password');
        $this->endpoint = config('const.switch.endpoint');
        $this->api_version = config('const.switch.api_version');
    }

    /**
     * Check if an e-mail address is already registered in an edu-ID account.
     *
     * @throws Exception
     */
    public function isEmailRegistered(string $email): bool
    {
        if (! $this->isConfigured()) {
            throw new Exception('The Switch service is not configured');
        }

        $response = Http::withBasicAuth($this->user, $this->password)
            ->get($this->endpoint.'/'.$this->api_version.'/mail/'.$email);

        if ($response->status() === 404 && $response->collect()->isEmpty()) {
            throw new Exception(trans('messages.switch.api.error'));
        }

        return match ($response->status()) {
            200 => true, // The email is registered
            404 => false, // The email is not registered
            default => throw new Exception(trans('messages.switch.api.error')),
        };
    }

    /**
     * Check if the user & password needed to use the service are available.
     */
    public static function isConfigured(): bool
    {
        return config('const.switch.user') && config('const.switch.password');
    }
}
