<?php

namespace App\Helpers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\ParameterBag;

class ControllerHelper
{
    private function __construct()
    {
    }

    public static function callAction(String $action, array $json = null, $request = null): Object
    {
        list($class, $method) = explode('@', $action);
        $controller = App::make('App\\Http\\Controllers\\' . $class);
        if (is_null($request)) {
            $request = new Request();
        }
        if (! is_null($json)) {
            $request->setJson(new ParameterBag($json));
        }
        $response = $controller->callAction($method, [$request]);
        if (! in_array($response->getStatusCode(), [200, 201])) {
            throw new Exception();
        }

        return $response->getData();
    }
}
