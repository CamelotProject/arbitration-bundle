<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Filter;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Filter
 *
 * @internal
 */
final class FilterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Filter::class, new Filter());
    }

    public function testRunGreyscale(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('greyscale')->once()->andReturn($mock)
                ->shouldReceive('brightness')->never()
                ->shouldReceive('contrast')->never()
                ->shouldReceive('colorize')->never();
        });

        static::assertInstanceOf(Image::class, (new Filter())->setParams(['filter' => 'greyscale'])->run($image));
    }

    public function testRunSepia(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock
                ->shouldReceive('greyscale')->once()->andReturn($mock)
                ->shouldReceive('brightness')->with(-10)->twice()->andReturn($mock)
                ->shouldReceive('contrast')->with(10)->twice()->andReturn($mock)
                ->shouldReceive('colorize')->with(38, 27, 12)->once()->andReturn($mock);
        });

        static::assertInstanceOf(Image::class, (new Filter())->setParams(['filter' => 'sepia'])->run($image));
    }

    public function testRunEmptyParams(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock
                ->shouldReceive('greyscale')->never()
                ->shouldReceive('brightness')->never()
                ->shouldReceive('contrast')->never()
                ->shouldReceive('colorize')->never();
        });

        static::assertInstanceOf(Image::class, (new Filter())->setParams([])->run($image));
    }

    public function testRunGreyscaleFilter(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('greyscale')->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, (new Filter())->runGreyscaleFilter($image));
    }

    public function testRunSepiaFilter(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('greyscale')->once()->andReturn($mock)
                ->shouldReceive('brightness')->with(-10)->twice()->andReturn($mock)
                ->shouldReceive('contrast')->with(10)->twice()->andReturn($mock)
                ->shouldReceive('colorize')->with(38, 27, 12)->once()->andReturn($mock);
        });

        static::assertInstanceOf(Image::class, (new Filter())->runSepiaFilter($image));
    }
}
