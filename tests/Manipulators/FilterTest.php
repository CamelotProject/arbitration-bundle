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
    private Filter $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Filter();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Filter::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('greyscale')->twice()->andReturn($mock)
                ->shouldReceive('brightness')->with(-10)->twice()->andReturn($mock)
                ->shouldReceive('contrast')->with(10)->twice()->andReturn($mock)
                ->shouldReceive('colorize')->with(38, 27, 12)->once()->andReturn($mock);
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['filter' => 'greyscale'])->run($image));

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['filter' => 'sepia'])->run($image));

        static::assertInstanceOf(Image::class, $this->manipulator->setParams([])->run($image));
    }

    public function testRunGreyscaleFilter(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('greyscale')->andReturn($mock)->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runGreyscaleFilter($image));
    }

    public function testRunSepiaFilter(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('greyscale')->once()->andReturn($mock)
                ->shouldReceive('brightness')->with(-10)->twice()->andReturn($mock)
                ->shouldReceive('contrast')->with(10)->twice()->andReturn($mock)
                ->shouldReceive('colorize')->with(38, 27, 12)->once()->andReturn($mock);
        });

        static::assertInstanceOf(Image::class, $this->manipulator->runSepiaFilter($image));
    }
}
