<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Camelot\Arbitration\Api\Intervene;
use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Manipulators;
use Intervention\Image\ImageManager;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(Manipulators\Orientation::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 10])
    ;
    $services->set(Manipulators\Crop::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 15])
    ;
    $services->set(Manipulators\Size::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 20])
    ;
    $services->set(Manipulators\Brightness::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 25])
    ;
    $services->set(Manipulators\Contrast::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 30])
    ;
    $services->set(Manipulators\Gamma::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 35])
    ;
    $services->set(Manipulators\Sharpen::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 40])
    ;
    $services->set(Manipulators\Filter::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 45])
    ;
    $services->set(Manipulators\Blur::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 50])
    ;
    $services->set(Manipulators\Pixelate::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 55])
    ;
    $services->set(Manipulators\Watermark::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 60])
    ;
    $services->set(Manipulators\Background::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 65])
    ;
    $services->set(Manipulators\Border::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 70])
    ;
    $services->set(Manipulators\Encode::class)
        ->tag('camelot.intervention.manipulator', ['priority' => 75])
    ;

    $services->set(ImageManager::class);

    $services->set(Intervene::class)
        ->arg('$manipulators', tagged_iterator('camelot.intervention.manipulator'))
    ;

    $services->alias(InterveneInterface::class, Intervene::class);

    $services->set(Renditions::class);
};
