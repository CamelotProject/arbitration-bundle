<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Gamma;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Gamma
 *
 * @internal
 */
final class GammaTest extends TestCase
{
    private Gamma $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Gamma();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Gamma::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('gamma')->with('1.5')->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['gamma' => '1.5'])->run($image));
    }

    public function testGetGamma(): void
    {
        static::assertSame(1.5, $this->manipulator->setParams(['gamma' => '1.5'])->getGamma());
        static::assertSame(1.5, $this->manipulator->setParams(['gamma' => 1.5])->getGamma());
        static::assertNull($this->manipulator->setParams(['gamma' => null])->getGamma());
        static::assertNull($this->manipulator->setParams(['gamma' => 'a'])->getGamma());
        static::assertNull($this->manipulator->setParams(['gamma' => '.1'])->getGamma());
        static::assertNull($this->manipulator->setParams(['gamma' => '9.999'])->getGamma());
        static::assertNull($this->manipulator->setParams(['gamma' => '0.005'])->getGamma());
        static::assertNull($this->manipulator->setParams(['gamma' => '-1'])->getGamma());
    }
}
