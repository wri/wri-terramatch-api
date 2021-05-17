<?php

namespace Tests\Feature;

use Tests\TestCase;

class DefaultControllerTest extends TestCase
{
    public function testIndexAction()
    {
        $response = $this->getJson("/");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJson([
            "data" => []
        ]);
    }
}
