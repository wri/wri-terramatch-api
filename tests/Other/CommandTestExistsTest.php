<?php

namespace Tests\Other;

use Tests\TestCase;

class CommandTestExistsTest extends TestCase
{
    public function testCommandsHaveTest()
    {
        $file = __DIR__ . "/../Feature/CommandsTest.php";
        $this->assertFileExists($file);
        $contents = file_get_contents($file);
        $this->assertTrue(class_exists("Tests\\Feature\\CommandsTest"));
        foreach ($this->getCommands() as &$command) {
            $command = basename($command, ".php");
            $signature = "public function test" . ucfirst($command) . "()";
            $this->assertStringContainsString($signature, $contents);
        }
    }
}
