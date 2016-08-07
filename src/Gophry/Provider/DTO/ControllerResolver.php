<?php

namespace Gophry\Provider\DTO;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

use \Silex\ControllerResolver as BaseControllerResolver;

use \Gophry\DTO\RequestDTOInterface;

class ControllerResolver extends BaseControllerResolver {

    protected function createController($controller) {
        if (false !== strpos($controller, '::')) {
            return parent::createController($controller);
        }

        if (false === strpos($controller, ':')) {
            throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
        }

        list($service, $method) = explode(':', $controller, 2);

        if (!isset($this->app[$service])) {
            throw new \InvalidArgumentException(sprintf('Service "%s" does not exist.', $controller));
        }
        
        return array($this->app[$service], $method);
    }
    
    protected function doGetArguments(Request $request, $controller, array $parameters) {
        $attributes = $request->attributes->all();
        $arguments = [];
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                if (PHP_VERSION_ID >= 50600 && $param->isVariadic() && is_array($attributes[$param->name])) {
                    $arguments = array_merge($arguments, array_values($attributes[$param->name]));
                } else {
                    $arguments[] = $attributes[$param->name];
                }
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            //<Extension for DTO>
            } elseif ($param instanceof \ReflectionParameter) {
                $class = $param->getClass();
                if ($class instanceof \ReflectionClass) {
                    $className = $class->getName();
                    $object = new $className();
                    $data = array_merge($request->query->all(), $request->request->all());
                    $object->bind($data);
                    $this->validate($object);
                    $arguments[] = $object;
                }
            //</Extension for DTO>
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }
		
        //$controller[0]->setAuthenticatedUser($request->user);
        
        return $arguments;
    }
    
    private function validate(RequestDTOInterface $dto) {
        $errors = $this->app['validator']->validate($dto);
        $hasError = count($errors) > 0;
        if ($hasError) {
            $exceptionClass = $this->app['gophry.invalid.request.exception.class'];
            throw new $exceptionClass('Invalid data', $errors);
        }
    }

}