<?php

use Camelot\Arbitration\Controller\SymfonyImageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('camelot_arbitration_render', '/render/{path}')
        ->controller(SymfonyImageController::class)
        ->methods([Request::METHOD_GET])
        ->requirements(['path' => '.+'])
    ;
};
