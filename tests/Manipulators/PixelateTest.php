<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Pixelate;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Pixelate
 *
 * @internal
 */
final class PixelateTest extends TestCase
{
    private Pixelate $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Pixelate();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Pixelate::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('pixelate')->with('10')->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['pixelate' => '10'])->run($image));
    }

    public function testGetPixelate(): void
    {
        static::assertSame(50, $this->manipulator->setParams(['pixelate' => '50'])->getPixelate());
        static::assertSame(50, $this->manipulator->setParams(['pixelate' => 50.50])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixelate' => null])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixelate' => 'a'])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixelate' => '-1'])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixelate' => '1001'])->getPixelate());
    }
}
