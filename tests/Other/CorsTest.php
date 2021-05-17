<?php

namespace Tests\Other;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function testCorsRequest()
    {
        $headers = [
            "origin" => "www.example.com"
        ];
        $response = $this->getJson("/api/auth/login", $headers);
        $response->assertHeader("Access-Control-Allow-Origin", "www.example.com");
    }
}
