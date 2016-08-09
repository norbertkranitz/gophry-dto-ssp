<?php

namespace Gophry\Provider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

use \Gophry\Core\Exception\UnprocessableEntityException;
use \Gophry\ControllerResolver;

class DTOServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['resolver'] = $app->factory(function ($app) {
            return new ControllerResolver($app);
        });

        $app['gophry.invalid.request.exception.class'] = UnprocessableEntityException::class;
    }

}
