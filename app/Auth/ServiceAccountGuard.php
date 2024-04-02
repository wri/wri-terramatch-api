<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Validators\TokenValidator;

class ServiceAccountGuard implements Guard
{
    use GuardHelpers;

    const HEADER = 'authorization';
    CONST PREFIX = 'bearer';
    const API_KEY_LENGTH = 64;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $apiKey = $this->getApiKey();
        if ($apiKey == null) {
            return null;
        }

        return $this->user = User::where('api_key', $apiKey)->first();
    }

    public function validate(array $credentials = []): bool
    {
        // There's no logging in or validating for this guard.
        return false;
    }

    protected function getApiKey(): ?string
    {
        $header = $this->request->headers->get(self::HEADER);
        if ($header == null) {
            return null;
        }

        $position = strripos($header, self::PREFIX);
        if ($position === false) {
            return null;
        }

        $bearerValue = trim(substr($header, $position + strlen(self::PREFIX)));
        if (strlen($bearerValue) !== self::API_KEY_LENGTH || $this->isJwt($bearerValue)) {
            return null;
        }

        return $bearerValue;
    }

    protected function isJwt($value): bool
    {
        try {
            return (new TokenValidator())->check($value) != null;
        } catch (TokenInvalidException $exception) {
            return false;
        }
    }
}