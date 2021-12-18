<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Pixelate;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
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

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['pixel' => '10'])->run($image));
    }

    public function testGetPixelate(): void
    {
        static::assertSame(50, $this->manipulator->setParams(['pixel' => '50'])->getPixelate());
        static::assertSame(50, $this->manipulator->setParams(['pixel' => 50.50])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixel' => null])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixel' => 'a'])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixel' => '-1'])->getPixelate());
        static::assertNull($this->manipulator->setParams(['pixel' => '1001'])->getPixelate());
    }
}
