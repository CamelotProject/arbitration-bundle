<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Size;
use Intervention\Image\Image;
use Mockery;
use Mockery\Matcher\Closure;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Size
 *
 * @internal
 */
final class SizeTest extends TestCase
{
    private Closure $callback;

    protected function setUp(): void
    {
        $this->callback = Mockery::on(fn () => true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Size::class, new Size());
    }

    public function testSetMaxImageSize(): void
    {
        $manipulator = new Size();
        $manipulator->setMaxImageSize(500 * 500);

        static::assertSame(500 * 500, $manipulator->getMaxImageSize());
    }

    public function testGetMaxImageSize(): void
    {
        static::assertNull((new Size())->getMaxImageSize());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn('200')->twice();
            $mock->shouldReceive('height')->andReturn('200')->once();
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->setParams(['width' => 100])->run($image));
    }

    public function providerWidth(): iterable
    {
        yield [100, ['width' => 100]];
        yield [100, ['width' => 100.1]];
        yield [null, ['width' => null]];
        yield [null, ['width' => 'a']];
        yield [null, ['width' => '-100']];
    }

    /** @dataProvider providerWidth */
    public function testGetWidth(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Size())->setParams($params)->getWidth());
    }

    public function providerHeight(): iterable
    {
        yield [100, ['height' => 100]];
        yield [100, ['height' => 100.1]];
        yield [null, ['height' => null]];
        yield [null, ['height' => 'a']];
        yield [null, ['height' => '-100']];
    }

    /** @dataProvider providerHeight */
    public function testGetHeight(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Size())->setParams($params)->getHeight());
    }

    public function providerFit(): iterable
    {
        yield ['contain', ['fit' => 'contain']];
        yield ['fill', ['fit' => 'fill']];
        yield ['max', ['fit' => 'max']];
        yield ['stretch', ['fit' => 'stretch']];
        yield ['crop', ['fit' => 'crop']];
        yield ['contain', ['fit' => 'invalid']];
    }

    /** @dataProvider providerFit */
    public function testGetFit(string $expected, array $params): void
    {
        static::assertSame($expected, (new Size())->setParams($params)->getFit());
    }

    public function providerCrop(): iterable
    {
        yield [[0, 0, 1.0], ['fit' => 'crop-top-left']];
        yield [[0, 100, 1.0], ['fit' => 'crop-bottom-left']];
        yield [[0, 50, 1.0], ['fit' => 'crop-left']];
        yield [[100, 0, 1.0], ['fit' => 'crop-top-right']];
        yield [[100, 100, 1.0], ['fit' => 'crop-bottom-right']];
        yield [[100, 50, 1.0], ['fit' => 'crop-right']];
        yield [[50, 0, 1.0], ['fit' => 'crop-top']];
        yield [[50, 100, 1.0], ['fit' => 'crop-bottom']];
        yield [[50, 50, 1.0], ['fit' => 'crop-center']];
        yield [[50, 50, 1.0], ['fit' => 'crop']];
        yield [[50, 50, 1.0], ['fit' => 'crop-center']];
        yield [[25, 75, 1.0], ['fit' => 'crop-25-75']];
        yield [[0, 100, 1.0], ['fit' => 'crop-0-100']];
        yield [[50, 50, 1.0], ['fit' => 'crop-101-102']];
        yield [[25, 75, 1.0], ['fit' => 'crop-25-75-1']];
        yield [[25, 75, 1.5], ['fit' => 'crop-25-75-1.5']];
        yield [[25, 75, 1.555], ['fit' => 'crop-25-75-1.555']];
        yield [[25, 75, 2.0], ['fit' => 'crop-25-75-2']];
        yield [[25, 75, 100.0], ['fit' => 'crop-25-75-100']];
        yield [[50, 50, 1.0], ['fit' => 'crop-25-75-101']];
        yield [[50, 50, 1.0], ['fit' => 'invalid']];
    }

    /** @dataProvider providerCrop */
    public function testGetCrop(?array $expected, array $params): void
    {
        static::assertSame($expected, (new Size())->setParams($params)->getCrop());
    }

    public function providerDpr(): iterable
    {
        yield [1.0, ['dpr' => 'invalid']];
        yield [1.0, ['dpr' => '-1']];
        yield [1.0, ['dpr' => '9']];
        yield [2.0, ['dpr' => '2']];
    }

    /** @dataProvider providerDpr */
    public function testGetDpr(?float $expected, array $params): void
    {
        static::assertSame($expected, (new Size())->setParams($params)->getDpr());
    }

    public function providerResolve(): iterable
    {
        yield [[400, 200], null, null];
        yield [[100, 50], 100, null];
        yield [[200, 100], null, 100];
    }

    /** @dataProvider providerResolve */
    public function testResolveMissingDimensions(array $expected, ?int $width, ?int $height): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(400);
            $mock->shouldReceive('height')->andReturn(200);
        });

        static::assertSame($expected, (new Size())->resolveMissingDimensions($image, $width, $height));
    }

    public function testResolveMissingDimensionsWithOddDimensions(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(1024);
            $mock->shouldReceive('height')->andReturn(553);
        });

        static::assertSame([411, 222], (new Size())->resolveMissingDimensions($image, 411, null));
    }

    public function providerLimitSize(): iterable
    {
        yield [[1000, 1000], null, 1000, 1000];
        yield [[1000, 1000], 1000 * 1000, 1000, 1000];
        yield [[500, 500], 500 * 500, 500, 500];
        yield [[500, 500], 500 * 500, 1000, 1000];
    }

    /** @dataProvider providerLimitSize */
    public function testLimitImageSize(?array $expected, ?int $maxImageSize, ?int $width, ?int $height): void
    {
        $size = new Size();
        $size->setMaxImageSize($maxImageSize);
        static::assertSame($expected, $size->limitImageSize($width, $height));
    }

    public function testRunResizeContain(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runResize($image, 'contain', 100, 100));
    }

    public function testRunResizeFill(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with(100, 100, 'center')->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runResize($image, 'fill', 100, 100));
    }

    public function testRunResizeMax(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runResize($image, 'max', 100, 100));
    }

    public function testRunResizeStretch(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runResize($image, 'stretch', 100, 100));
    }

    public function testRunResizeCrop(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->times(4);
            $mock->shouldReceive('height')->andReturn(100)->times(4);
            $mock->shouldReceive('crop')->andReturn($mock)->once();
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runResize($image, 'crop', 100, 100));
    }

    public function testRunResizeInvalid(): void
    {
        static::assertInstanceOf(Image::class, (new Size())->runResize(Mockery::mock(Image::class), 'invalid', 100, 100));
    }

    public function testRunContainResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runContainResize($image, 100, 100));
    }

    public function testRunFillResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with(100, 100, 'center')->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runFillResize($image, 100, 100));
    }

    public function testRunMaxResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runMaxResize($image, 100, 100));
    }

    public function testRunStretchResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resize')->with(100, 100)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runStretchResize($image, 100, 100));
    }

    public function testRunCropResize(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->times(4);
            $mock->shouldReceive('height')->andReturn(100)->times(4);
            $mock->shouldReceive('resize')->with(100, 100, $this->callback)->andReturn($mock)->once();
            $mock->shouldReceive('crop')->with(100, 100, 0, 0)->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Size())->runCropResize($image, 100, 100, 'center'));
    }
}
