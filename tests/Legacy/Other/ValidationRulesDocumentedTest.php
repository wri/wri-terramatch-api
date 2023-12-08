<?php

namespace Tests\Legacy\Other;

use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Tests\Legacy\LegacyTestCase;

final class ValidationRulesDocumentedTest extends LegacyTestCase
{
    public function testValidationExtensionsPresentInSwagger(): void
    {
        $yaml = $this->get('/documentation/raw')->getContent();
        $swagger = Yaml::parse($yaml);
        $description = $swagger['info']['description'];

        $extensions = glob(__DIR__ . '/../../../app/Validators/Extensions/*.php');
        foreach ($extensions as $extension) {
            $basename = basename($extension, '.php');
            if ($basename == 'Extension') {
                continue;
            }
            $name = strtoupper(Str::snake($basename));
            $this->assertStringContainsString($name, $description);
        }
    }
}
