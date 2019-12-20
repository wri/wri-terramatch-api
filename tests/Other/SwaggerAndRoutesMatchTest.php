<?php

namespace Tests\Other;

use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class SwaggerAndRoutesMatchTest extends TestCase
{
    public function testSwaggerAndRoutesMatch()
    {
        $swagger = Yaml::parseFile(__DIR__ . "/../../swagger.yml");
        $routes = file_get_contents(__DIR__ . "/../../routes/api.php");
        foreach ($swagger["paths"] as $path => $methods) {
            foreach ($methods as $method => $details) {
                $route = "Route::" . $method . "(\"" . strtolower($path) . "\",";
                $this->assertStringContainsString($route, $routes);
            }
        }
    }
}
