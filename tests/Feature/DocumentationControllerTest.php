<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationControllerTest extends TestCase
{
    public function testReadAsHtmlAction()
    {
        $response = $this->get("/documentation");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "text/html; charset=UTF-8");
        $response->assertHeader("Content-Type", "text/html; charset=UTF-8");
        $response->assertSee("<div id=\"swagger-ui\"></div>");
    }

    public function testReadAsYamlAction()
    {
        $response = $this->get("/documentation/raw");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "text/plain; charset=UTF-8");
        $response->assertSeeText("swagger: \"2.0\"");
    }
}
