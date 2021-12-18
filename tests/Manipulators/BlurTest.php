<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Blur;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Intervention\Manipulators\BaseManipulator
 * @covers \Camelot\Intervention\Manipulators\Blur
 *
 * @internal
 */
final class BlurTest extends TestCase
{
    private Blur $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Blur();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Blur::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('blur')->with('10')->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['blur' => 10])->run($image));
    }

    public function testGetBlur(): void
    {
        static::assertSame(50, $this->manipulator->setParams(['blur' => '50'])->getBlur());
        static::assertSame(50, $this->manipulator->setParams(['blur' => 50])->getBlur());
        static::assertNull($this->manipulator->setParams(['blur' => null])->getBlur());
        static::assertNull($this->manipulator->setParams(['blur' => 'a'])->getBlur());
        static::assertNull($this->manipulator->setParams(['blur' => '-1'])->getBlur());
        static::assertNull($this->manipulator->setParams(['blur' => '101'])->getBlur());
    }
}
