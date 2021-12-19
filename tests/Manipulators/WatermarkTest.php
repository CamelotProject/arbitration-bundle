<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Watermark;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Image;
use League\Flysystem\FilesystemOperator;
use League\Glide\Filesystem\FilesystemException;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Watermark
 *
 * @internal
 */
final class WatermarkTest extends TestCase
{
    private Watermark $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Watermark(Mockery::mock(FilesystemOperator::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Watermark::class, $this->manipulator);
    }

    public function testSetWatermarks(): void
    {
        $this->manipulator->setWatermarks(Mockery::mock(FilesystemOperator::class));
        static::assertInstanceOf(FilesystemOperator::class, $this->manipulator->getWatermarks());
    }

    public function testGetWatermarks(): void
    {
        static::assertInstanceOf(FilesystemOperator::class, $this->manipulator->getWatermarks());
    }

    public function testSetWatermarksPathPrefix(): void
    {
        $this->manipulator->setWatermarksPathPrefix('watermarks/');
        static::assertSame('watermarks', $this->manipulator->getWatermarksPathPrefix());
    }

    public function testGetWatermarksPathPrefix(): void
    {
        static::assertSame('', $this->manipulator->getWatermarksPathPrefix());
    }

    public function testRun(): void
    {
        static::markTestIncomplete();

        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('insert')->once();
            $mock->shouldReceive('getDriver')->andReturn(Mockery::mock(AbstractDriver::class, function ($mock): void {
                $mock->shouldReceive('init')->with('content')->andReturn(Mockery::mock(Image::class, function ($mock): void {
                    $mock->shouldReceive('width')->andReturn(0)->once();
                    $mock->shouldReceive('resize')->once();
                }))->once();
            }))->once();
        });

        $this->manipulator->setWatermarks(Mockery::mock(FilesystemOperator::class, function ($watermarks): void {
            $watermarks->shouldReceive('fileExists')->with('image.jpg')->andReturn(true)->once();
            $watermarks->shouldReceive('read')->with('image.jpg')->andReturn('content')->once();
        }));

        $this->manipulator->setParams([
            'mark' => 'image.jpg',
            'markw' => '100',
            'markh' => '100',
            'markpad' => '10',
        ]);

        static::assertInstanceOf(Image::class, $this->manipulator->run($image));
    }

    /** @doesNotPerformAssertions */
    public function testGetImage(): void
    {
        static::markTestIncomplete();

        $this->manipulator->getWatermarks()
            ->shouldReceive('fileExists')
            ->with('watermarks/image.jpg')
            ->andReturn(true)
            ->once()
            ->shouldReceive('read')
            ->with('watermarks/image.jpg')
            ->andReturn('content')
            ->once()
        ;

        $this->manipulator->setWatermarksPathPrefix('watermarks');

        $driver = Mockery::mock(AbstractDriver::class);
        $driver->shouldReceive('init')
            ->with('content')
            ->andReturn(Mockery::mock(Image::class))
            ->once()
        ;

        $image = Mockery::mock(Image::class);
        $image->shouldReceive('getDriver')
            ->andReturn($driver)
            ->once()
        ;

        $this->manipulator->setParams(['mark' => 'image.jpg'])->getImage($image);
    }

    public function testGetImageWithUnreadableSource(): void
    {
        static::markTestIncomplete();

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessage('Could not read the image `image.jpg`.');

        $this->manipulator->getWatermarks()
            ->shouldReceive('fileExists')
            ->with('image.jpg')
            ->andReturn(true)
            ->once()
            ->shouldReceive('read')
            ->with('image.jpg')
            ->andThrow('League\Flysystem\UnableToReadFile')
            ->once()
        ;

        $image = Mockery::mock(Image::class);

        $this->manipulator->setParams(['mark' => 'image.jpg'])->getImage($image);
    }

    public function testGetImageWithoutMarkParam(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertNull($this->manipulator->getImage($image));
    }

    public function testGetImageWithEmptyMarkParam(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertNull($this->manipulator->setParams(['mark' => ''])->getImage($image));
    }

    public function testGetImageWithoutWatermarksFilesystem(): void
    {
        $this->manipulator->setWatermarks(null);

        $image = Mockery::mock(Image::class);

        static::assertNull($this->manipulator->setParams(['mark' => 'image.jpg'])->getImage($image));
    }

    public function providerDimensions(): iterable
    {
        yield [300.0, ['w' => '300']];
        yield [300.0, ['w' => 300]];
        yield [1000.0, ['w' => '50w']];
        yield [500.0, ['w' => '50h']];
        yield [null, ['w' => '101h']];
        yield [null, ['w' => -1]];
        yield [null, ['w' => '']];
    }

    /** @dataProvider providerDimensions */
    public function testGetDimension(?float $expected, array $params): void
    {
        $image = Mockery::mock(Image::class);
        $image->shouldReceive('width')->andReturn(2000);
        $image->shouldReceive('height')->andReturn(1000);

        static::assertSame($expected, $this->manipulator->setParams($params)->getDimension($image, 'w'));
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
        static::assertSame($expected, $this->manipulator->setParams($params)->getDpr());
    }

    public function providerFit(): iterable
    {
        yield ['contain', ['watermark_fit' => 'contain']];
        yield ['max', ['watermark_fit' => 'max']];
        yield ['stretch', ['watermark_fit' => 'stretch']];
        yield ['crop', ['watermark_fit' => 'crop']];
        yield ['crop-top-left', ['watermark_fit' => 'crop-top-left']];
        yield ['crop-top', ['watermark_fit' => 'crop-top']];
        yield ['crop-top-right', ['watermark_fit' => 'crop-top-right']];
        yield ['crop-left', ['watermark_fit' => 'crop-left']];
        yield ['crop-center', ['watermark_fit' => 'crop-center']];
        yield ['crop-right', ['watermark_fit' => 'crop-right']];
        yield ['crop-bottom-left', ['watermark_fit' => 'crop-bottom-left']];
        yield ['crop-bottom', ['watermark_fit' => 'crop-bottom']];
        yield ['crop-bottom-right', ['watermark_fit' => 'crop-bottom-right']];
        yield [null, ['watermark_fit' => null]];
        yield [null, ['watermark_fit' => 'invalid']];
    }

    /** @dataProvider providerFit */
    public function testGetFit(?string $expected, array $params): void
    {
        static::assertSame($expected, $this->manipulator->setParams($params)->getFit());
    }

    public function providerPosition(): iterable
    {
        yield ['top-left', ['watermark_position' => 'top-left']];
        yield ['top', ['watermark_position' => 'top']];
        yield ['top-right', ['watermark_position' => 'top-right']];
        yield ['left', ['watermark_position' => 'left']];
        yield ['center', ['watermark_position' => 'center']];
        yield ['right', ['watermark_position' => 'right']];
        yield ['bottom-left', ['watermark_position' => 'bottom-left']];
        yield ['bottom', ['watermark_position' => 'bottom']];
        yield ['bottom-right', ['watermark_position' => 'bottom-right']];
        yield ['bottom-right', []];
        yield ['bottom-right', ['watermark_position' => 'invalid']];
    }

    /** @dataProvider providerPosition */
    public function testGetPosition(?string $expected, array $params): void
    {
        static::assertSame($expected, $this->manipulator->setParams($params)->getPosition());
    }

    public function providerAlpha(): iterable
    {
        yield [100, ['watermark_alpha' => 'invalid']];
        yield [100, ['watermark_alpha' => 255]];
        yield [100, ['watermark_alpha' => -1]];
        yield [65, ['watermark_alpha' => '65']];
        yield [65, ['watermark_alpha' => 65]];
    }

    /** @dataProvider providerAlpha */
    public function testGetAlpha(?int $expected, array $params): void
    {
        static::assertSame($expected, $this->manipulator->setParams($params)->getAlpha());
    }
}
