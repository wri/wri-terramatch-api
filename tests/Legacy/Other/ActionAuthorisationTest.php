<?php

namespace Tests\Legacy\Other;

use Tests\Legacy\LegacyTestCase;

final class ActionAuthorisationTest extends LegacyTestCase
{
    public function testActionsContainAuthoriseCall(): void
    {
        foreach ($this->getControllers() as $controller) {
            $actions = $this->getActions($controller);
            foreach ($actions as $action) {
                $body = $this->getActionBody($action);
                $this->assertStringContainsString('$this->authorize(', $body);
            }
        }
    }
}
