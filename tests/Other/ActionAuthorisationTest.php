<?php

namespace Tests\Other;

use Tests\TestCase;

class ActionAuthorisationTest extends TestCase
{
    public function testActionsContainAuthoriseCall()
    {
        foreach ($this->getControllers() as $controller) {
            $actions = $this->getActions($controller);
            foreach ($actions as $action) {
                $body = $this->getActionBody($action);
                $this->assertStringContainsString("\$this->authorize(", $body);
            }
        }
    }
}
