<?php

namespace Gophry\Provider\DTO;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

class DTOServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['resolver'] = $app->factory(function ($app) {
            return new ControllerResolver($app);
        });

        $app['gophry.invalid.request.exception.class'] = \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException::class;
    }

}
