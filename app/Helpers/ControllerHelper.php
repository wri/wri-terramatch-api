<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Exception;

class ControllerHelper
{
    private function __construct()
    {
    }

    public static function callAction(String $action, Array $json = null): Object
    {
        list($class, $method) = explode("@", $action);
        $controller = App::make("App\\Http\\Controllers\\" . $class);
        $request = new Request();
        if (!is_null($json)) {
            $request->setJson(new ParameterBag($json));
        }
        $response = $controller->callAction($method, [$request]);
        if (!in_array($response->getStatusCode(), [200, 201])) {
            throw new Exception();
        }
        return $response->getData();
    }
}
