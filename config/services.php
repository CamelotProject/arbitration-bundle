<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Camelot\Arbitration\Api\Intervene;
use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Controller\SymfonyImageController;
use Camelot\Arbitration\Filesystem\Filesystem;
use Camelot\Arbitration\Filesystem\Finder;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use Camelot\Arbitration\Generator\SizesMediaQueryGenerator;
use Camelot\Arbitration\Generator\SourceGenerator;
use Camelot\Arbitration\Generator\SourceSetGenerator;
use Camelot\Arbitration\Manipulators;
use Camelot\Arbitration\Message\ImageRenderHandler;
use Camelot\Arbitration\Responder\FilesystemResponder;
use Camelot\Arbitration\Responder\Psr16Responder;
use Camelot\Arbitration\Responder\Psr6Responder;
use Camelot\Arbitration\Responder\Responder;
use Camelot\Arbitration\Responder\ResponderInterface;
use Camelot\Arbitration\ResponseFactory\ResponseFactoryInterface;
use Camelot\Arbitration\ResponseFactory\SymfonyResponseFactory;
use Camelot\Arbitration\Twig\ArbitrationExtension;
use Intervention\Image\ImageManager;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$imagesFilesystem', service('camelot.intervention.filesystem.images'))
        ->bind('$renderFilesystem', service('camelot.intervention.filesystem.render'))
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
        ->call('setFilesystem', [service('camelot.intervention.filesystem.images')])
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

    $services->set('camelot.intervention.filesystem.images', Filesystem::class);

    $services->set('camelot.intervention.filesystem.render', Filesystem::class);

    $services->set(Finder::class);

    $services->set(PathnameGenerator::class);

    $services->alias(PathnameGeneratorInterface::class, PathnameGenerator::class);

    $services->set(SourceGenerator::class);

    $services->set(SourceSetGenerator::class);

    $services->set(SizesMediaQueryGenerator::class);

    $services->set(Responder::class);

    $services->set(FilesystemResponder::class);

    $services->set(Psr6Responder::class);

    $services->set(Psr16Responder::class);

    $services->alias(ResponderInterface::class, FilesystemResponder::class);

    $services->set(SymfonyResponseFactory::class)
        ->arg('$maxAge', 31536000)
    ;

    $services->alias(ResponseFactoryInterface::class, SymfonyResponseFactory::class);

    $services->set(SymfonyImageController::class)
        ->tag('controller.service_arguments')
    ;

    $services->set(ImageRenderHandler::class);

    $services->set(ArbitrationExtension::class);
};
