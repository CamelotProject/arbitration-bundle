<?php

declare(strict_types=1);

namespace Camelot\Arbitration;

use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Generator\SourceGenerator;
use Camelot\Arbitration\Generator\SourceSetGenerator;
use Camelot\Arbitration\Responder\ResponderInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class CamelotArbitrationBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $builder->getDefinition(Renditions::class)
            ->setArgument('$renditions', $config['renditions'])
            ->setArgument('$sets', $config['sets'])
        ;

        $builder->getDefinition('camelot.intervention.filesystem.images')
            ->setArgument('$basePath', $config['image_path'])
        ;

        $builder->getDefinition('camelot.intervention.filesystem.render')
            ->setArgument('$basePath', $config['render_path'])
        ;

        $builder->getDefinition(SourceGenerator::class)
            ->setArgument('$imagesPath', $config['image_path'])
            ->setArgument('$renderPath', $config['render_path'])
        ;

        $builder->getDefinition(SourceSetGenerator::class)
            ->setArgument('$imagesPath', $config['image_path'])
            ->setArgument('$renderPath', $config['render_path'])
        ;

        if (is_a($config['responder'], ResponderInterface::class, true)) {
            $builder->setAlias(ResponderInterface::class, $config['responder']);
        }
    }
}
