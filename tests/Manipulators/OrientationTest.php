<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Orientation;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OrientationTest extends TestCase
{
    private Orientation $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Orientation();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf('League\Glide\Manipulators\Orientation', $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('orientate')->andReturn($mock)->once();
            $mock->shouldReceive('rotate')->andReturn($mock)->with('90')->once();
        });

        static::assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['or' => 'auto'])->run($image)
        );

        static::assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['or' => '90'])->run($image)
        );
    }

    public function testGetOrientation(): void
    {
        static::assertSame('auto', $this->manipulator->setParams(['or' => 'auto'])->getOrientation());
        static::assertSame('0', $this->manipulator->setParams(['or' => '0'])->getOrientation());
        static::assertSame('90', $this->manipulator->setParams(['or' => '90'])->getOrientation());
        static::assertSame('180', $this->manipulator->setParams(['or' => '180'])->getOrientation());
        static::assertSame('270', $this->manipulator->setParams(['or' => '270'])->getOrientation());
        static::assertSame('auto', $this->manipulator->setParams(['or' => null])->getOrientation());
        static::assertSame('auto', $this->manipulator->setParams(['or' => '1'])->getOrientation());
        static::assertSame('auto', $this->manipulator->setParams(['or' => '45'])->getOrientation());
    }
}
