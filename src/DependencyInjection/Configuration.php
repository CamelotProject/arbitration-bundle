<?php

declare(strict_types=1);

namespace Camelot\Arbitration\DependencyInjection;

use Camelot\Arbitration\Responder\Responder;
use Camelot\Arbitration\Util\Ratio;
use Closure;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use function sprintf;

final class Configuration implements ConfigurationInterface
{
    private static string $defaultFormat = 'webp';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('camelot_arbitration');
        $rootNode = $treeBuilder->getRootNode();

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
        $this->addRenditions($defaultChildren);

        // Renditions & sets
        $rootChildren
            ->append($this->getRenditions())
            ->append($this->getSets())
        ;

        $rootNode->validate()
            ->always(function (array $config) {
                foreach ($config['renditions'] as $name => $rendition) {
                    foreach ($rendition as $item => $value) {
                        if ($item === 'width' || $item === 'height') {
                            continue;
                        }
                        $config['renditions'][$name][$item] ??= $config['defaults'][$item] ?? null;
                    }
                    $config['renditions'][$name]['format'] ??= $config['defaults']['format'] ?? self::$defaultFormat;
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

        return $treeBuilder;
    }

    private function getSets(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('sets');
        $renditions = $treeBuilder->getRootNode();
        $renditions
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('renditions')
                        ->scalarPrototype()->end()
                        ->info('List of rendition names to assign to the set')//->end()
                    ->end()
                    ->arrayNode('media_queries')
                        ->scalarPrototype()->end()
                        ->info('List of media queries for the set')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $renditions;
    }

    private function getRenditions(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('renditions');
        $renditions = $treeBuilder->getRootNode();

        $children = $renditions
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()->always(Closure::fromCallable([Ratio::class, 'normaliseOptions']))->end()
                ->children()
        ;
        $this->addRenditions($children);

        return $renditions;
    }

    private function addRenditions(NodeBuilder $children): void
    {
        $children
            ->floatNode('dpr')
                ->defaultValue(1.0)
                ->min(0)
                ->max(8)
                ->info('Device pixel ratio.')
            ->end()
            ->scalarNode('format')
                ->defaultNull()
                ->info("Encodes the image to a specific format. Accepts 'jpg', 'pjpg' (progressive jpeg), 'png', 'gif', 'webp' or 'avif'. Defaults to 'jpg'.")
            ->end()
            ->scalarNode('quality')
                ->defaultNull()
                ->info("Defines the quality of the image. Use values between 0 and 100. Defaults to 90. Only relevant if the format is set to 'jpg' or 'pjpg'.")
            ->end()
            ->scalarNode('max_size')
                ->defaultNull()
                ->info("Limit the maximum pixels size (count) of generated images, i.e. width multiplied by height.")
            ->end()
        ;
        $this->addLayoutNodes($children);
        $this->addColourNodes($children);
        $this->addEffectsNodes($children);

        $children->append($this->watermarkNodeDefinition());
    }

    private function addLayoutNodes(NodeBuilder $children): void
    {
        $children
            ->scalarNode('ratio')
                ->defaultNull()
                ->info("The ratio of these image's width to height. e.g.: '4:3', '4x3', '1.333'.")
            ->end()
            ->scalarNode('width')
                ->defaultNull()
                ->info('Sets the width of the image, in pixels.')
            ->end()
            ->scalarNode('height')
                ->defaultNull()
                ->info('Sets the height of the image, in pixels.')
            ->end()
            ->enumNode('orientation')
                ->values([null, 'auto', 0, 90, 180, 270])
                ->defaultNull()
                ->info("Rotates the image. Accepts 'auto', 0, 90, 180 or 270. Default is 'auto'. The auto option uses Exif data to automatically orient images correctly.")
            ->end()
            ->scalarNode('crop')
                ->defaultNull()
                ->info('Crops the image to specific dimensions prior to any other resize operations. Required format: width,height,x,y')
            ->end()
            ->enumNode('fit')
                ->values([null, 'contain', 'max', 'fill', 'stretch', 'crop'])
                ->defaultNull()
                ->info('Sets how the image is fitted to its target dimensions. ' . self::getFitAccepts())
            ->end()
            ->enumNode('flip')
                ->values([null, 'v', 'h', 'both'])
                ->defaultNull()
                ->info("Flips the image. Accepts 'v', 'h' and 'both'.")
            ->end()
        ;
    }

    private function addColourNodes(NodeBuilder $children): void
    {
        $children
            ->scalarNode('background')
                ->defaultNull()
                ->info('Sets the background color of the image. Accepts hexadecimal RGB and RBG alpha formats.')
            ->end()
            ->scalarNode('brightness')
                ->defaultNull()
                ->validate()->ifTrue(fn($v) => (int) $v < -100 || (int) $v > 100)->then(fn () => throw new InvalidConfigurationException())->end()
                ->info('Adjusts the image brightness. Use values between -100 and +100, where 0 represents no change.')
            ->end()
            ->scalarNode('contrast')
                ->defaultNull()
                ->validate()->ifTrue(fn($v) => (int) $v < -100 || (int) $v > 100)->then(fn () => throw new InvalidConfigurationException())->end()
                ->info('Adjusts the image contrast. Use values between -100 and +100, where 0 represents no change.')
            ->end()
            ->scalarNode('gamma')
                ->defaultNull()
                ->beforeNormalization()->always(fn($v) => $v === null ? null : (float) $v)->end()
                ->validate()->ifTrue(fn($v) => (int) $v < 0.1 || (int) $v > 9.99)->then(fn () => throw new InvalidConfigurationException())->end()
                ->info('Adjusts the image gamma. Use values between 0.1 and 9.99.')
            ->end()
        ;
    }

    private function addEffectsNodes(NodeBuilder $children): void
    {
        $children
            ->scalarNode('blur')
                ->defaultNull()
                ->validate()->ifTrue(fn($v) => (int) $v < 0 || (int) $v > 100)->then(fn () => throw new InvalidConfigurationException())->end()
                ->info('Adds a blur effect to the image. Use values between 0 and 100.')
            ->end()
            ->enumNode('filter')
                ->values([null, 'greyscale', 'sepia'])
                ->defaultNull()
                ->info("Applies a filter effect to the image. Accepts 'greyscale' or 'sepia'.")
            ->end()
            ->scalarNode('pixelate')
                ->defaultNull()
                ->validate()->ifTrue(fn($v) => (int) $v < 0 || (int) $v > 1000)->then(fn () => throw new InvalidConfigurationException())->end()
                ->info('Applies a pixelation effect to the image. Use values between 0 and 1000.')
            ->end()
            ->scalarNode('sharpen')
                ->defaultNull()
                ->validate()->ifTrue(fn($v) => (int) $v < 0 || (int) $v > 100)->then(fn () => throw new InvalidConfigurationException())->end()
                ->info('Sharpen the image. Use values between 0 and 100.')
            ->end()
        ;
    }

    private function watermarkNodeDefinition(): ArrayNodeDefinition|NodeDefinition
    {
        $treeBuilder = new TreeBuilder('watermark');
        $watermark = $treeBuilder->getRootNode();
        $watermark
            ->info('Adds a watermark to the image.')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('path')
                    ->info('Path to an image to be used as the watermark')
                    ->isRequired()
                ->end()
                ->scalarNode('width')
                    ->defaultNull()
                    ->info('Sets the width of the watermark in pixels, or using relative dimensions.')
                ->end()
                ->scalarNode('height')
                    ->defaultNull()
                    ->info('Sets the height of the watermark in pixels, or using relative dimensions.')
                ->end()
                ->enumNode('fit')
                    ->values([null, 'contain', 'max', 'fill', 'stretch', 'crop'])
                    ->defaultNull()
                    ->info('Sets how the watermark is fitted to its target dimensions. ' . self::getFitAccepts())
                ->end()
                ->scalarNode('offset_x')
                    ->defaultNull()
                    ->info("Sets how far the watermark is away from the left and right edges of the image. Set in pixels, or using relative dimensions. Ignored if 'position' is set to 'center'.")
                ->end()
                ->scalarNode('offset_y')
                    ->defaultNull()
                    ->info("Sets how far the watermark is away from the top and bottom edges of the image. Set in pixels, or using relative dimensions. Ignored if 'position' is set to 'center'.")
                ->end()
                ->scalarNode('padding')
                    ->defaultNull()
                    ->info("Sets how far the watermark is away from edges of the image. Basically a shortcut for using both 'offset_x' and 'offset_x'. Set in pixels, or using relative dimensions. Ignored if 'position' is set to 'center'.")
                ->end()
                ->scalarNode('position')
                    ->defaultNull()
                    ->info("Sets where the watermark is positioned. Accepts 'top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'. Default is 'center'.")
                ->end()
                ->scalarNode('alpha')
                    ->defaultNull()
                    ->validate()->always(fn($v) => (int) $v >= 0 && (int) $v <= 100)->end()
                    ->info('Sets the opacity of the watermark. Use values between 0 and 100, where 100 is fully opaque, and 0 is fully transparent.')
                ->end()
            ->end()
        ;

        return $watermark;
    }

    private static function getFitAccepts(): string
    {
        return 'Accepts:
 - contain: (default) Resizes the image to fit within the width and height boundaries without cropping, distorting or
            altering the aspect ratio.
 - max:     Resizes the image to fit within the width and height boundaries without cropping, distorting or altering
            the aspect ratio, and will also not increase the size of the image if it is smaller than the output size.
 - fill:    Resizes the image to fit within the width and height boundaries without cropping or distorting the image,
            and the remaining space is filled with the background color. The resulting image will match the
            constraining dimensions.
 - stretch: Stretches the image to fit the constraining dimensions exactly. The resulting image will fill the
            dimensions, and will not maintain the aspect ratio of the input image.
 - crop:    Resizes the image to fill the width and height boundaries and crops any excess image data. The resulting
            image will match the width and height constraints without distorting the image.';
    }
}
