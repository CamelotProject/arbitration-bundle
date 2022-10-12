<?php

declare(strict_types=1);

namespace Symfony\Config;

use Camelot\Arbitration\Responder\Responder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\Exception;

return static function (DefinitionConfigurator $definition) {
    require_once __DIR__ . '/definitions/legacy.php';

    $defaultFormat = 'webp';

    $rootNode = $definition->rootNode();

    $rootNode->addDefaultsIfNotSet();
    $rootChildren = $rootNode->children();

    // Standard
    $rootChildren
        ->enumNode('driver')
            ->values(['gd', 'imagick'])
            ->defaultValue('gd')
        ->end()

        ->scalarNode('image_path')->defaultValue('%kernel.project_dir%/public')->end()
        ->scalarNode('render_path')->defaultValue('%kernel.project_dir%/public/render')->end()
        ->scalarNode('responder')->defaultValue(Responder::class)->end()
    ;

    // Rendition defaults
    $defaultChildren = $rootChildren
        ->arrayNode('defaults')
            ->addDefaultsIfNotSet()
//                ->beforeNormalization()->always(Closure::fromCallable([Ratio::class, 'normaliseOptions']))->end()
            ->children()
    ;
    addRenditions($defaultChildren);

    // Renditions & sets
    $rootChildren
        ->append(getRenditions())
        ->append(getSets())
    ;

    $rootNode->validate()
        ->always(function (array $config) use ($defaultFormat) {
            foreach ($config['renditions'] as $name => $rendition) {
                foreach ($rendition as $item => $value) {
                    if ($item === 'width' || $item === 'height') {
                        continue;
                    }
                    $config['renditions'][$name][$item] ??= $config['defaults'][$item] ?? null;
                }
                $config['renditions'][$name]['format'] ??= $config['defaults']['format'] ?? $defaultFormat;
            }

            return $config;
        })
    ;

    $rootNode->validate()
        ->always(function (array $config) {
            foreach ($config['sets'] ?? [] as $name => $set) {
                foreach ($set['renditions'] ?? [] as $rendition) {
                    if (isset($config['renditions'][$rendition])) {
                        continue;
                    }

                    throw new Exception(sprintf('Set %s has an invalid rendition name "%s"', $name, $rendition));
                }
            }

            return $config;
        })
    ;
};
