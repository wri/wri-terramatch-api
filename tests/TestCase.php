<?php

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getHeadersForUser($email)
    {
        $token = Auth::attempt([
            'email_address' => $email,
            'password' => 'password',
        ]);

        return [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
    }
}
