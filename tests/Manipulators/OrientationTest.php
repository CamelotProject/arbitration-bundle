<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Orientation;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Orientation
 *
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
        static::assertInstanceOf(Orientation::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('orientate')->andReturn($mock)->once();
            $mock->shouldReceive('rotate')->andReturn($mock)->with('90')->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['orientation' => 'auto'])->run($image));

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['orientation' => '90'])->run($image));
    }

    public function testGetOrientation(): void
    {
        static::assertSame('auto', $this->manipulator->setParams(['orientation' => 'auto'])->getOrientation());
        static::assertSame('0', $this->manipulator->setParams(['orientation' => '0'])->getOrientation());
        static::assertSame('90', $this->manipulator->setParams(['orientation' => '90'])->getOrientation());
        static::assertSame('180', $this->manipulator->setParams(['orientation' => '180'])->getOrientation());
        static::assertSame('270', $this->manipulator->setParams(['orientation' => '270'])->getOrientation());
        static::assertSame('auto', $this->manipulator->setParams(['orientation' => null])->getOrientation());
        static::assertSame('auto', $this->manipulator->setParams(['orientation' => '1'])->getOrientation());
        static::assertSame('auto', $this->manipulator->setParams(['orientation' => '45'])->getOrientation());
    }
}
