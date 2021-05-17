<?php

namespace Tests\Other;

use Tests\TestCase;

class ControllerTestExistsTest extends TestCase
{
    public function testControllersHaveTest()
    {
        foreach ($this->getControllers() as $controller) {
            $test = basename(str_replace("\\", "/", $controller)) . "Test";
            $file = __DIR__ . "/../Feature/" . $test . ".php";
            $this->assertFileExists($file);
        }
    }
}
