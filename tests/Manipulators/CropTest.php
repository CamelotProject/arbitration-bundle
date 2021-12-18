<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Crop;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Intervention\Manipulators\BaseManipulator
 * @covers \Camelot\Intervention\Manipulators\Crop
 *
 * @internal
 */
final class CropTest extends TestCase
{
    private Crop $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Crop();
        $this->image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100);
            $mock->shouldReceive('height')->andReturn(100);
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Crop::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $this->image->shouldReceive('crop')->with(100, 100, 0, 0)->once();

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['crop' => '100,100,0,0'])->run($this->image));
    }

    public function testGetCoordinates(): void
    {
        static::assertSame([100, 100, 0, 0], $this->manipulator->setParams(['crop' => '100,100,0,0'])->getCoordinates($this->image));
        static::assertSame([101, 1, 1, 1], $this->manipulator->setParams(['crop' => '101,1,1,1'])->getCoordinates($this->image));
        static::assertSame([1, 101, 1, 1], $this->manipulator->setParams(['crop' => '1,101,1,1'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => null])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => '1,1,1,'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => '1,1,,1'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => '1,,1,1'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => ',1,1,1'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => '-1,1,1,1'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => '1,1,101,1'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => '1,1,1,101'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => 'a'])->getCoordinates($this->image));
        static::assertNull($this->manipulator->setParams(['crop' => ''])->getCoordinates($this->image));
    }

    public function testValidateCoordinates(): void
    {
        static::assertSame([100, 100, 0, 0], $this->manipulator->limitToImageBoundaries($this->image, [100, 100, 0, 0]));
        static::assertSame([90, 90, 10, 10], $this->manipulator->limitToImageBoundaries($this->image, [100, 100, 10, 10]));
    }
}
