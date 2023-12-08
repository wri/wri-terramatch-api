<?php

namespace Tests\Legacy\Other;

use Tests\Legacy\LegacyTestCase;

final class ActionTypeHintsTest extends LegacyTestCase
{
    public function testActionsUseTypeHints(): void
    {
        foreach ($this->getControllers() as $controller) {
            $actions = $this->getActions($controller);
            foreach ($actions as $action) {
                $response = $action->getReturnType()->getName();
                $responses = [
                    'Illuminate\\Http\\Response',
                    'Illuminate\\Http\\JsonResponse',
                    'Illuminate\\Http\\RedirectResponse',
                    'Illuminate\\View\\View',
                    'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
                    'Symfony\\Component\\HttpFoundation\\StreamedResponse',
                    'App\\Http\\Resources\\V2\\User\\MeResource',
                ];
                $this->assertIsOneOf($responses, $response);
                $parameters = $action->getParameters();
                $this->assertTrue(count($parameters) > 0);
            }
        }
    }
}
