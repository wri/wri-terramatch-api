<?php

namespace Tests\Legacy\Other;

use PHPUnit\Framework\Constraint\TraversableContainsEqual;
use Symfony\Component\Yaml\Yaml;
use Tests\Legacy\LegacyTestCase;

final class RoutesAndSwaggerMatchTest extends LegacyTestCase
{

    public function testRoutesAndSwaggerMatch(): void
    {
        $swagger = Yaml::parseFile(__DIR__ . "/../../../resources/docs/swagger.yml");
        $paths = [];
        foreach ($swagger['paths'] as $path => $methods) {
            foreach ($methods as $method => $details) {
                $paths[$method][] = strtolower($path);
            }
        }
        $routes = file_get_contents(__DIR__ . "/../../../routes/api.php");
        $matches = [];
        preg_match_all('/\\nRoute::[a-z]{2,6}\\([^\\)]+\\);/', $routes, $matches);
        foreach ($matches[0] as $match) {
            $a = explode('::', trim($match));
            $b = explode('(', $a[1]);
            $c = explode(', ', $b[1]);
            $method = $b[0];
            $path = strtolower(trim($c[0], "'\""));
            $normalisedPath = preg_replace('/{[a-zA-Z0-9?]+}/', '{id}', $path);
            $this->assertArrayHasKey($method, $paths);
            $toExclude = [
                '/carbon_certification_types',
                '/organisation_types',
                '/land_ownerships',
                '/land_sizes',
                '/continents',
                '/restoration_goals',
                '/funding_sources',
                '/reporting_frequencies',
                '/reporting_levels',
                '/sustainable_development_goals',
                '/rejected_reasons',
            ];
            if(!in_array($path, $toExclude)) {
                $this->assertThat(
                    $paths[$method],
                    $this->logicalOr(
                        new TraversableContainsEqual($path),
                        new TraversableContainsEqual($normalisedPath)
                    )
                );
            }
        }
    }
}
