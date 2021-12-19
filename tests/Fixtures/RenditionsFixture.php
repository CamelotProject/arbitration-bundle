<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Fixtures;

use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

final class RenditionsFixture
{
    public static function createFixture(): Renditions
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [Yaml::parseFile(__DIR__ . '/config.yaml')]);

        return new Renditions($config['renditions'], $config['sets']);
    }
}
