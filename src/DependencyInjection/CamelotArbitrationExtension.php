<?php

declare(strict_types=1);

namespace Camelot\Arbitration\DependencyInjection;

use Camelot\Arbitration\Configuration\Renditions;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use function dirname;

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
    }
}
