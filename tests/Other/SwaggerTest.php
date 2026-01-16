<?php

namespace Tests\Other;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

#[Group('swagger')]
final class SwaggerTest extends TestCase
{
    public function test_v2_valid_yaml(): void
    {
        $file = __DIR__ . '/../../resources/docs/swagger-v2.yml';
        $this->assertTrue(is_file($file));
        $this->assertTrue(is_readable($file));
        $swagger = Yaml::parseFile($file);
        $this->assertIsArray($swagger);
        $this->assertArrayHasKey('swagger', $swagger);
        $this->assertEquals('2.0', $swagger['swagger']);
    }

    public function test_v2_valid_swagger(): void
    {
        $yaml = $this->get('/documentation/v2/raw')->getContent();
        $swagger = Yaml::parse($yaml);
        $json = json_encode($swagger);
        File::put('swagger-v2.json', $json);
        $file = base_path() . '/swagger-v2.json';
        $validator = 'https://validator.swagger.io/validator/debug';
        $response = shell_exec('curl -s -X POST -d @' . $file . " -H 'Content-Type:application/json' " . $validator);
        $this->assertNotEmpty($response);
        $results = json_decode($response);
        $this->assertIsObject($results);
        if (property_exists($results, 'schemaValidationMessages')) {
            $this->assertEmpty($results->schemaValidationMessages);
        }
        if (property_exists($results, 'messages')) {
            dd($results->messages);
        }
        File::delete('swagger-v2.json');
    }
}
