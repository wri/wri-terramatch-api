<?php

namespace Tests\Other;

use Illuminate\Support\Facades\File;
use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class SwaggerTest extends TestCase
{
    public function testValidYaml()
    {
        $file = __DIR__ . "/../../swagger.yml";
        $this->assertTrue(is_file($file));
        $this->assertTrue(is_readable($file));
        $swagger = Yaml::parseFile($file);
        $this->assertIsArray($swagger);
        $this->assertArrayHasKey("swagger", $swagger);
        $this->assertEquals("2.0", $swagger["swagger"]);
    }

    public function testValidSwagger()
    {
        $yaml = $this->get("/documentation/raw")->getContent();
        $swagger = Yaml::parse($yaml);
        $json = json_encode($swagger);
        File::put("swagger.json", $json);
        $file = base_path() . "/swagger.json";
        $validator = "http://validator.swagger.io/validator/debug";
        $response = shell_exec("curl -s -X POST -d @" . $file . " -H 'Content-Type:application/json' " . $validator);
        $this->assertNotEmpty($response);
        $results = json_decode($response);
        $this->assertIsObject($results);
        $this->assertFalse(property_exists($results, "messages"));
        $this->assertFalse(property_exists($results, "schemaValidationMessages"));
        File::delete("swagger.json");
    }
}
