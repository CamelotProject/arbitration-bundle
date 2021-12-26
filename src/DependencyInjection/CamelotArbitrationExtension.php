<?php

declare(strict_types=1);

namespace Camelot\Arbitration\DependencyInjection;

use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Generator\SourceGenerator;
use Camelot\Arbitration\Generator\SourceSetGenerator;
use Camelot\Arbitration\Responder\ResponderInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use function dirname;
use function is_a;

final class CamelotArbitrationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config'));
        $loader->load('services.php');

        $config = (new Processor())->processConfiguration(new Configuration(), $configs);

        $container->getDefinition(Renditions::class)
            ->setArgument('$renditions', $config['renditions'])
            ->setArgument('$sets', $config['sets'])
        ;

        $container->getDefinition('camelot.intervention.filesystem.images')
            ->setArgument('$basePath', $config['image_path'])
        ;

        $container->getDefinition('camelot.intervention.filesystem.render')
            ->setArgument('$basePath', $config['render_path'])
        ;

        $container->getDefinition(SourceGenerator::class)
            ->setArgument('$imagesPath', $config['image_path'])
            ->setArgument('$renderPath', $config['render_path'])
        ;

        $container->getDefinition(SourceSetGenerator::class)
            ->setArgument('$imagesPath', $config['image_path'])
            ->setArgument('$renderPath', $config['render_path'])
        ;

        if (is_a($config['responder'], ResponderInterface::class, true)) {
            $container->setAlias(ResponderInterface::class, $config['responder']);
        }
    }
}
