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
                    \Illuminate\Http\JsonResponse::class,
                    \Illuminate\Http\Response::class,
                    \Illuminate\View\View::class,
                    \Symfony\Component\HttpFoundation\BinaryFileResponse::class
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
