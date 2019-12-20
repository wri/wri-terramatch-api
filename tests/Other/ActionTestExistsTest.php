<?php

namespace Tests\Other;

use Tests\TestCase;

class ActionTestExistsTest extends TestCase
{
    public function testActionsHaveTest()
    {
        foreach ($this->getControllers() as $controller) {
            $test = basename(str_replace("\\", "/", $controller)) . "Test";
            $file = __DIR__ . "/../Feature/" . $test . ".php";
            $contents = @file_get_contents($file);
            $actions = $this->getActions($controller);
            foreach ($actions as $action) {
                $signature = "public function test" . ucfirst($action->name) . "()";
                $this->assertStringContainsString($signature, $contents);
            }
        }
    }
}
