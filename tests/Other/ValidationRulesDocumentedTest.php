<?php

namespace Tests\Other;

use Illuminate\Support\Str;
use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class ValidationRulesDocumentedTest extends TestCase
{
    public function testValidationExtensionsPresentInSwagger()
    {
        $yaml = $this->get("/documentation/raw")->getContent();
        $swagger = Yaml::parse($yaml);
        $description = $swagger["info"]["description"];

        $extensions = glob(__DIR__ . "/../../app/Validators/Extensions/*.php");
        foreach ($extensions as $extension) {
            $basename = basename($extension, ".php");
            if ($basename == "Extension") {
                continue;
            }
            $name = strtoupper(Str::snake($basename));
            $this->assertStringContainsString($name, $description);
        }
    }
}
