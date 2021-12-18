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
 * @covers \Camelot\Intervention\Manipulators\BaseManipulator
 * @covers \Camelot\Intervention\Manipulators\Watermark
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

    public function testGetDimension(): void
    {
        $image = Mockery::mock(Image::class);
        $image->shouldReceive('width')->andReturn(2000);
        $image->shouldReceive('height')->andReturn(1000);

        static::assertSame(300.0, $this->manipulator->setParams(['w' => '300'])->getDimension($image, 'w'));
        static::assertSame(300.0, $this->manipulator->setParams(['w' => 300])->getDimension($image, 'w'));
        static::assertSame(1000.0, $this->manipulator->setParams(['w' => '50w'])->getDimension($image, 'w'));
        static::assertSame(500.0, $this->manipulator->setParams(['w' => '50h'])->getDimension($image, 'w'));
        static::assertNull($this->manipulator->setParams(['w' => '101h'])->getDimension($image, 'w'));
        static::assertNull($this->manipulator->setParams(['w' => -1])->getDimension($image, 'w'));
        static::assertNull($this->manipulator->setParams(['w' => ''])->getDimension($image, 'w'));
    }

    public function testGetDpr(): void
    {
        static::assertSame(1.0, $this->manipulator->setParams(['dpr' => 'invalid'])->getDpr());
        static::assertSame(1.0, $this->manipulator->setParams(['dpr' => '-1'])->getDpr());
        static::assertSame(1.0, $this->manipulator->setParams(['dpr' => '9'])->getDpr());
        static::assertSame(2.0, $this->manipulator->setParams(['dpr' => '2'])->getDpr());
    }

    public function testGetFit(): void
    {
        static::assertSame('contain', $this->manipulator->setParams(['markfit' => 'contain'])->getFit());
        static::assertSame('max', $this->manipulator->setParams(['markfit' => 'max'])->getFit());
        static::assertSame('stretch', $this->manipulator->setParams(['markfit' => 'stretch'])->getFit());
        static::assertSame('crop', $this->manipulator->setParams(['markfit' => 'crop'])->getFit());
        static::assertSame('crop-top-left', $this->manipulator->setParams(['markfit' => 'crop-top-left'])->getFit());
        static::assertSame('crop-top', $this->manipulator->setParams(['markfit' => 'crop-top'])->getFit());
        static::assertSame('crop-top-right', $this->manipulator->setParams(['markfit' => 'crop-top-right'])->getFit());
        static::assertSame('crop-left', $this->manipulator->setParams(['markfit' => 'crop-left'])->getFit());
        static::assertSame('crop-center', $this->manipulator->setParams(['markfit' => 'crop-center'])->getFit());
        static::assertSame('crop-right', $this->manipulator->setParams(['markfit' => 'crop-right'])->getFit());
        static::assertSame('crop-bottom-left', $this->manipulator->setParams(['markfit' => 'crop-bottom-left'])->getFit());
        static::assertSame('crop-bottom', $this->manipulator->setParams(['markfit' => 'crop-bottom'])->getFit());
        static::assertSame('crop-bottom-right', $this->manipulator->setParams(['markfit' => 'crop-bottom-right'])->getFit());
        static::assertNull($this->manipulator->setParams(['markfit' => null])->getFit());
        static::assertNull($this->manipulator->setParams(['markfit' => 'invalid'])->getFit());
    }

    public function testGetPosition(): void
    {
        static::assertSame('top-left', $this->manipulator->setParams(['markpos' => 'top-left'])->getPosition());
        static::assertSame('top', $this->manipulator->setParams(['markpos' => 'top'])->getPosition());
        static::assertSame('top-right', $this->manipulator->setParams(['markpos' => 'top-right'])->getPosition());
        static::assertSame('left', $this->manipulator->setParams(['markpos' => 'left'])->getPosition());
        static::assertSame('center', $this->manipulator->setParams(['markpos' => 'center'])->getPosition());
        static::assertSame('right', $this->manipulator->setParams(['markpos' => 'right'])->getPosition());
        static::assertSame('bottom-left', $this->manipulator->setParams(['markpos' => 'bottom-left'])->getPosition());
        static::assertSame('bottom', $this->manipulator->setParams(['markpos' => 'bottom'])->getPosition());
        static::assertSame('bottom-right', $this->manipulator->setParams(['markpos' => 'bottom-right'])->getPosition());
        static::assertSame('bottom-right', $this->manipulator->setParams([])->getPosition());
        static::assertSame('bottom-right', $this->manipulator->setParams(['markpos' => 'invalid'])->getPosition());
    }

    public function testGetAlpha(): void
    {
        static::assertSame(100, $this->manipulator->setParams(['markalpha' => 'invalid'])->getAlpha());
        static::assertSame(100, $this->manipulator->setParams(['markalpha' => 255])->getAlpha());
        static::assertSame(100, $this->manipulator->setParams(['markalpha' => -1])->getAlpha());
        static::assertSame(65, $this->manipulator->setParams(['markalpha' => '65'])->getAlpha());
        static::assertSame(65, $this->manipulator->setParams(['markalpha' => 65])->getAlpha());
    }
}
