<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Size;
use Intervention\Image\Image;
use Mockery;
use Mockery\Matcher\Closure;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SizeTest extends TestCase
{
    private Size $manipulator;
    private Closure $callback;

    protected function setUp(): void
    {
        $this->manipulator = new Size();
        $this->callback = Mockery::on(fn () => true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Size::class, $this->manipulator);
    }

    public function testSetMaxImageSize(): void
    {
        $this->manipulator->setMaxImageSize(500 * 500);
        static::assertSame(500 * 500, $this->manipulator->getMaxImageSize());
    }

    public function testGetMaxImageSize(): void
    {
        static::assertNull($this->manipulator->getMaxImageSize());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn('200')->twice();
            $mock->shouldReceive('height')->andReturn('200')->once();
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['w' => 100])->run($image));
    }

    public function testGetWidth(): void
    {
        static::assertSame(100, $this->manipulator->setParams(['w' => 100])->getWidth());
        static::assertSame(100, $this->manipulator->setParams(['w' => 100.1])->getWidth());
        static::assertNull($this->manipulator->setParams(['w' => null])->getWidth());
        static::assertNull($this->manipulator->setParams(['w' => 'a'])->getWidth());
        static::assertNull($this->manipulator->setParams(['w' => '-100'])->getWidth());
    }

    public function testGetHeight(): void
    {
        static::assertSame(100, $this->manipulator->setParams(['h' => 100])->getHeight());
        static::assertSame(100, $this->manipulator->setParams(['h' => 100.1])->getHeight());
        static::assertNull($this->manipulator->setParams(['h' => null])->getHeight());
        static::assertNull($this->manipulator->setParams(['h' => 'a'])->getHeight());
        static::assertNull($this->manipulator->setParams(['h' => '-100'])->getHeight());
    }

    public function testGetFit(): void
    {
        static::assertSame('contain', $this->manipulator->setParams(['fit' => 'contain'])->getFit());
        static::assertSame('fill', $this->manipulator->setParams(['fit' => 'fill'])->getFit());
        static::assertSame('max', $this->manipulator->setParams(['fit' => 'max'])->getFit());
        static::assertSame('stretch', $this->manipulator->setParams(['fit' => 'stretch'])->getFit());
        static::assertSame('crop', $this->manipulator->setParams(['fit' => 'crop'])->getFit());
        static::assertSame('contain', $this->manipulator->setParams(['fit' => 'invalid'])->getFit());
    }

    public function testGetCrop(): void
    {
        static::assertSame([0, 0, 1.0], $this->manipulator->setParams(['fit' => 'crop-top-left'])->getCrop());
        static::assertSame([0, 100, 1.0], $this->manipulator->setParams(['fit' => 'crop-bottom-left'])->getCrop());
        static::assertSame([0, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop-left'])->getCrop());
        static::assertSame([100, 0, 1.0], $this->manipulator->setParams(['fit' => 'crop-top-right'])->getCrop());
        static::assertSame([100, 100, 1.0], $this->manipulator->setParams(['fit' => 'crop-bottom-right'])->getCrop());
        static::assertSame([100, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop-right'])->getCrop());
        static::assertSame([50, 0, 1.0], $this->manipulator->setParams(['fit' => 'crop-top'])->getCrop());
        static::assertSame([50, 100, 1.0], $this->manipulator->setParams(['fit' => 'crop-bottom'])->getCrop());
        static::assertSame([50, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop-center'])->getCrop());
        static::assertSame([50, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop'])->getCrop());
        static::assertSame([50, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop-center'])->getCrop());
        static::assertSame([25, 75, 1.0], $this->manipulator->setParams(['fit' => 'crop-25-75'])->getCrop());
        static::assertSame([0, 100, 1.0], $this->manipulator->setParams(['fit' => 'crop-0-100'])->getCrop());
        static::assertSame([50, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop-101-102'])->getCrop());
        static::assertSame([25, 75, 1.0], $this->manipulator->setParams(['fit' => 'crop-25-75-1'])->getCrop());
        static::assertSame([25, 75, 1.5], $this->manipulator->setParams(['fit' => 'crop-25-75-1.5'])->getCrop());
        static::assertSame([25, 75, 1.555], $this->manipulator->setParams(['fit' => 'crop-25-75-1.555'])->getCrop());
        static::assertSame([25, 75, 2.0], $this->manipulator->setParams(['fit' => 'crop-25-75-2'])->getCrop());
        static::assertSame([25, 75, 100.0], $this->manipulator->setParams(['fit' => 'crop-25-75-100'])->getCrop());
        static::assertSame([50, 50, 1.0], $this->manipulator->setParams(['fit' => 'crop-25-75-101'])->getCrop());
        static::assertSame([50, 50, 1.0], $this->manipulator->setParams(['fit' => 'invalid'])->getCrop());
    }

    public function testGetDpr(): void
    {
        static::assertSame(1.0, $this->manipulator->setParams(['dpr' => 'invalid'])->getDpr());
        static::assertSame(1.0, $this->manipulator->setParams(['dpr' => '-1'])->getDpr());
        static::assertSame(1.0, $this->manipulator->setParams(['dpr' => '9'])->getDpr());
        static::assertSame(2.0, $this->manipulator->setParams(['dpr' => '2'])->getDpr());
    }

    public function testResolveMissingDimensions(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(400);
            $mock->shouldReceive('height')->andReturn(200);
        });

        static::assertSame([400, 200], $this->manipulator->resolveMissingDimensions($image, null, null));
        static::assertSame([100, 50], $this->manipulator->resolveMissingDimensions($image, 100, null));
        static::assertSame([200, 100], $this->manipulator->resolveMissingDimensions($image, null, 100));
    }

    public function testResolveMissingDimensionsWithOddDimensions(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(1024);
            $mock->shouldReceive('height')->andReturn(553);
        });

        static::assertSame([411, 222], $this->manipulator->resolveMissingDimensions($image, 411, null));
    }

    public function testLimitImageSize(): void
    {
        static::assertSame([1000, 1000], $this->manipulator->limitImageSize(1000, 1000));
        $this->manipulator->setMaxImageSize(500 * 500);
        static::assertSame([500, 500], $this->manipulator->limitImageSize(500, 500));
        static::assertSame([500, 500], $this->manipulator->limitImageSize(1000, 1000));
    }

    public function testRunResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->times(4);
            $mock->shouldReceive('height')->andReturn(100)->times(4);
            $mock->shouldReceive('crop')->andReturn($mock)->once();
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->times(4);
            $mock->shouldReceive('resize')->with(100, 100)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with(100, 100, 'center')->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runResize($image, 'contain', 100, 100));

        static::assertInstanceOf(Image::class, $this->manipulator->runResize($image, 'fill', 100, 100));

        static::assertInstanceOf(Image::class, $this->manipulator->runResize($image, 'max', 100, 100));

        static::assertInstanceOf(Image::class, $this->manipulator->runResize($image, 'stretch', 100, 100));

        static::assertInstanceOf(Image::class, $this->manipulator->runResize($image, 'crop', 100, 100));

        static::assertInstanceOf(Image::class, $this->manipulator->runResize($image, 'invalid', 100, 100));
    }

    public function testRunContainResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runContainResize($image, 100, 100));
    }

    public function testRunFillResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with(100, 100, 'center')->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runFillResize($image, 100, 100));
    }

    public function testRunMaxResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runMaxResize($image, 100, 100));
    }

    public function testRunStretchResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runStretchResize($image, 100, 100));
    }

    public function testRunCropResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->times(4);
            $mock->shouldReceive('height')->andReturn(100)->times(4);
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
            $mock->shouldReceive('crop')->with(100, 100, 0, 0)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runCropResize($image, 100, 100, 'center'));
    }
}
