<?php

namespace Tests\Other;

use Tests\TestCase;

class ActionTypeHintsTest extends TestCase
{
    public function testActionsUseTypeHints()
    {
        foreach ($this->getControllers() as $controller) {
            $actions = $this->getActions($controller);
            foreach ($actions as $action) {
                $response = $action->getReturnType()->getName();
                $responses = [
                    "Illuminate\\Http\\Response",
                    "Illuminate\\Http\\JsonResponse",
                    "Illuminate\\Http\\RedirectResponse",
                    "Illuminate\\View\\View",
                    "Symfony\\Component\\HttpFoundation\\BinaryFileResponse"
                ];
                $this->assertIsOneOf($responses, $response);
                $parameters = $action->getParameters();
                $this->assertTrue(count($parameters) > 0);
                $request = $parameters[0]->getType()->getName();
                $this->assertSame("Illuminate\\Http\\Request", $request);
            }
        }
    }
}
