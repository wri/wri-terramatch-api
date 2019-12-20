<?php

namespace Tests\Other;

use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class RoutesAndSwaggerMatchTest extends TestCase
{
    public function testRoutesAndSwaggerMatch()
    {
        $routes = file_get_contents(__DIR__ . "/../../routes/api.php");
        $matches = [];
        $lines = [];
        preg_match_all("/\\nRoute::[a-z]{2,8}\\([^\\)]+\\);/", $routes, $matches);
        foreach ($matches[0] as $match) {
            $lines[] = trim($match);
        }
        $swagger = Yaml::parseFile(__DIR__ . "/../../swagger.yml");
        $paths = [];
        foreach ($swagger["paths"] as $path => $methods) {
            foreach ($methods as $method => $details) {
                $paths[$method][] = strtolower($path);
            }
        }
        foreach ($lines as $line) {
            $a = explode("::", $line);
            $b = explode("(", $a[1]);
            $c = explode(", ", $b[1]);
            $method = $b[0];
            $path = trim($c[0], "'\"");
            $this->assertArrayHasKey($method, $paths);
            $this->assertInArray($path, $paths[$method]);
        }
    }
}
