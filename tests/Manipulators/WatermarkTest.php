<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Filesystem\FilesystemInterface;
use Camelot\Arbitration\Manipulators\Watermark;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Watermark
 *
 * @internal
 */
final class WatermarkTest extends TestCase
{
    private Watermark $manipulator;
    private FilesystemInterface $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = Mockery::mock(FilesystemInterface::class);
        $this->manipulator = new Watermark();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Watermark::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('insert')->once();
            $mock->shouldReceive('getDriver')->andReturn(Mockery::mock(AbstractDriver::class, function ($mock): void {
                $mock->shouldReceive('init')->with('content')->andReturn(Mockery::mock(Image::class, function ($mock): void {
                    $mock->shouldReceive('width')->andReturn(0)->once();
                    $mock->shouldReceive('resize')->andReturn($mock)->once();
                    $mock->shouldReceive('opacity')->with(75)->once();
                }))->once();
            }))->once();
        });

        $this->filesystem
            ->expects('exists')
            ->once()
            ->andReturnTrue()
        ;
        $this->filesystem
            ->expects('readFile')
            ->once()
            ->andReturn('content')
        ;

        $this->manipulator->setFilesystem($this->filesystem);
        $this->manipulator->setParams([
            'watermark_path' => 'image.jpg',
            'watermark_width' => '100',
            'watermark_height' => '100',
            'watermark_padding' => '10',
            'watermark_alpha' => '75',
        ]);

        static::assertInstanceOf(Image::class, $this->manipulator->run($image));
    }

    /** @doesNotPerformAssertions */
    public function testGetImage(): void
    {
        $this->filesystem
            ->expects('exists')
            ->with('image.jpg')
            ->once()
            ->andReturnTrue()
        ;
        $this->filesystem
            ->expects('readFile')
            ->with('image.jpg')
            ->once()
            ->andReturn('content')
        ;
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

        $this->manipulator->setFilesystem($this->filesystem);
        $this->manipulator->setParams(['watermark_path' => 'image.jpg'])->getImage($image);
    }

    public function testGetImageWithUnreadableSource(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Failed to read file "image.jpg"');

        $this->filesystem
            ->expects('exists')
            ->with('image.jpg')
            ->once()
            ->andReturnTrue()
        ;
        $this->filesystem
            ->expects('readFile')
            ->with('image.jpg')
            ->once()
            ->andThrow(IOException::class, 'Failed to read file "image.jpg"')
        ;

        $driver = Mockery::mock(AbstractDriver::class);
        $driver->shouldReceive('init')
            ->with('content')
            ->andReturn(Mockery::mock(Image::class))
        ;

        $image = Mockery::mock(Image::class);
        $image->shouldReceive('getDriver')
            ->andReturn($driver)
            ->once()
        ;

        $this->manipulator->setFilesystem($this->filesystem);
        $this->manipulator->setParams(['watermark_path' => 'image.jpg'])->getImage($image);
    }

    public function testGetImageWithoutMarkParam(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertNull($this->manipulator->getImage($image));
    }

    public function testGetImageWithEmptyMarkParam(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertNull($this->manipulator->setParams(['watermark_path' => ''])->getImage($image));
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
