<?php

namespace Tests\Legacy\Feature;

use Tests\Legacy\LegacyTestCase;

final class DocumentationControllerTest extends LegacyTestCase
{
    public function testReadAsHtmlAction(): void
    {
        $response = $this->get('/documentation');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('<redoc spec-url="/documentation/raw"></redoc>', false);
    }

    public function testReadSwaggerAsHtmlAction(): void
    {
        $response = $this->get('/documentation/swagger');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('<div id="swagger-ui"></div>', false);
    }

    public function testReadAsYamlAction(): void
    {
        $response = $this->get('/documentation/raw');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSeeText("swagger: '2.0'", false);
    }
}
