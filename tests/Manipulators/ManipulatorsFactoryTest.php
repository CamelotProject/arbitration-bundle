<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\ManipulatorInterface;
use Camelot\Arbitration\Manipulators\ManipulatorsFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\ManipulatorsFactory
 *
 * @internal
 */
final class ManipulatorsFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $manipulators = ManipulatorsFactory::create();

        static::assertCount(14, $manipulators);

        foreach ($manipulators as $manipulator) {
            static::assertInstanceOf(ManipulatorInterface::class, $manipulator);
        }
    }
}
