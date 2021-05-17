<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Testing\File;
use ReflectionClass;
use ReflectionMethod;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function getControllers(): array
    {
        $controllers = glob(__DIR__ . "/../app/Http/Controllers/*.php");
        $controllers = array_filter($controllers, function($controller) {
            return !in_array(basename($controller, ".php"), ["Controller"]);
        });
        foreach ($controllers as &$controller) {
            $controller = "App\\Http\\Controllers\\" . basename($controller, ".php");
        }
        return $controllers;
    }

    protected function getCommands(): array
    {
        $commands = glob(__DIR__ . "/../app/Console/Commands/*.php");
        $commands = array_filter($commands, function($command) {
            $internalCommands = ["MigrateServicesCommand", "CreateFramesCommand", "CreateAdminCommand", "CheckQueueLengthCommand"];
            return !in_array(basename($command, ".php"), $internalCommands);
        });
        foreach ($commands as &$command) {
            $controller = "App\\Console\\Commands\\" . basename($command, ".php");
        }
        return $commands;
    }

    protected function getActions(string $controller): array
    {
        $reflection = new ReflectionClass($controller);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_filter($methods, function($method) {
            return substr($method->name, -6) == "Action" && $method->name != "callAction";
        });
    }

    protected function getActionBody(ReflectionMethod $action): string
    {
        $class = file_get_contents($action->getFileName());
        $lines = explode("\n", $class);
        $start = $action->getStartLine();
        $end = $action->getEndLine();
        $body = implode("\n", array_slice($lines, $start, $end - $start));
        return $body;
    }

    protected function assertIsOneOf(array $haystack, $needle): void
    {
        $message = "Failed asserting that " . $needle . " is one of " . implode(", ", $haystack) . ".";
        $this->assertTrue(in_array($needle, $haystack), $message);
    }

    protected function assertInArray($needle, array $haystack): void
    {
        $message = "Failed asserting that " . $needle . " is in array " . implode(", ", $haystack) . ".";
        $this->assertTrue(in_array($needle, $haystack), $message);
    }

    protected function fakeImage()
    {
        return new File("image.png", fopen(__DIR__ . "/../resources/seeds/image.png", "r"));
    }

    protected function fakeCorruptedImage()
    {
        return new \Illuminate\Http\UploadedFile(__DIR__ . "/../resources/seeds/corrupted_image.png", 'pdf-as-png.png', 'image/png');
    }

    protected function fakeMap()
    {
        return new File("map.tiff", fopen(__DIR__ . "/../resources/seeds/map.tiff", "r"));
    }

    protected function fakeFile()
    {
        return new File("file.pdf", fopen(__DIR__ . "/../resources/seeds/file.pdf", "r"));
    }

    protected function fakeVideo()
    {
        return new File("video.mp4", fopen(__DIR__ . "/../resources/seeds/video.mp4", "r"));
    }

    protected function searchCodebase(string $search, string $directory): array
    {
        $search = "\"" . $search . "\"";
        $directory = "\"" . $directory . "\"";
        $output = shell_exec("grep -r " . $search . " " . $directory);
        $lines = explode("\n", $output);
        return array_filter($lines, function($value) {
            return trim($value) != "";
        });
    }
}
