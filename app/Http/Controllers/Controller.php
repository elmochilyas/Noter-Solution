<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use ReflectionMethod;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    public function callAction($method, $parameters): mixed
    {
        $route = request()->route();
        $routeParams = $route ? $route->parametersWithoutNulls() : [];

        if ($routeParams) {
            $ref = new ReflectionMethod($this, $method);
            $resolved = [];

            foreach ($ref->getParameters() as $param) {
                $name = $param->getName();

                if (array_key_exists($name, $routeParams)) {
                    $resolved[] = $routeParams[$name];
                } elseif ($param->hasType() && ! $param->getType()->isBuiltin()) {
                    $className = $param->getType()->getName();

                    if ($className === Request::class) {
                        $resolved[] = request();
                    } elseif ($param->isDefaultValueAvailable()) {
                        $resolved[] = $param->getDefaultValue();
                    } else {
                        $resolved[] = app($className);
                    }
                } elseif ($param->isDefaultValueAvailable()) {
                    $resolved[] = $param->getDefaultValue();
                } else {
                    $resolved[] = null;
                }
            }

            $parameters = $resolved;
        }

        return parent::callAction($method, $parameters);
    }
}
