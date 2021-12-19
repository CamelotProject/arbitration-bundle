<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Contrast;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Contrast
 *
 * @internal
 */
final class ContrastTest extends TestCase
{
    private Contrast $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Contrast();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Contrast::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('contrast')->with('50')->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['contrast' => 50])->run($image));
    }

    public function testGetPixelate(): void
    {
        static::assertSame(50, $this->manipulator->setParams(['contrast' => '50'])->getContrast());
        static::assertSame(50, $this->manipulator->setParams(['contrast' => 50])->getContrast());
        static::assertNull($this->manipulator->setParams(['contrast' => null])->getContrast());
        static::assertNull($this->manipulator->setParams(['contrast' => '101'])->getContrast());
        static::assertNull($this->manipulator->setParams(['contrast' => '-101'])->getContrast());
        static::assertNull($this->manipulator->setParams(['contrast' => 'a'])->getContrast());
    }
}
