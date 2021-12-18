<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Border;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BorderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf('League\Glide\Manipulators\Border', new Border());
    }

    public function testGetBorder(): void
    {
        $image = Mockery::mock('Intervention\Image\Image');

        $border = new Border();

        static::assertNull($border->getBorder($image));

        static::assertSame(
            [10.0, 'rgba(0, 0, 0, 1)', 'overlay'],
            $border->setParams(['border' => '10,black'])->getBorder($image)
        );
    }

    public function testGetInvalidBorder(): void
    {
        $image = Mockery::mock('Intervention\Image\Image');

        $border = new Border();

        static::assertNull(
            $border->setParams(['border' => '0,black'])->getBorder($image)
        );
    }

    public function testGetWidth(): void
    {
        $image = Mockery::mock('Intervention\Image\Image');

        $border = new Border();

        static::assertSame(100.0, $border->getWidth($image, 1, '100'));
    }

    public function testGetColor(): void
    {
        $border = new Border();

        static::assertSame('rgba(0, 0, 0, 1)', $border->getColor('black'));
    }

    public function testGetMethod(): void
    {
        $border = new Border();

        static::assertSame('expand', $border->getMethod('expand'));
        static::assertSame('shrink', $border->getMethod('shrink'));
        static::assertSame('overlay', $border->getMethod('overlay'));
        static::assertSame('overlay', $border->getMethod('invalid'));
    }

    public function testGetDpr(): void
    {
        $border = new Border();

        static::assertSame(1.0, $border->setParams(['dpr' => 'invalid'])->getDpr());
        static::assertSame(1.0, $border->setParams(['dpr' => '-1'])->getDpr());
        static::assertSame(1.0, $border->setParams(['dpr' => '9'])->getDpr());
        static::assertSame(2.0, $border->setParams(['dpr' => '2'])->getDpr());
    }

    public function testRunWithNoBorder(): void
    {
        $image = Mockery::mock('Intervention\Image\Image');

        $border = new Border();

        static::assertInstanceOf('Intervention\Image\Image', $border->run($image));
    }

    public function testRunOverlay(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->once();
            $mock->shouldReceive('height')->andReturn(100)->once();
            $mock->shouldReceive('rectangle')->with(5, 5, 95, 95, Mockery::on(fn ($closure) => true))->andReturn($mock)->once();
        });

        $border = new Border();
        $border->setParams(['border' => '10,5000,overlay']);

        static::assertInstanceOf('Intervention\Image\Image', $border->run($image));
    }

    public function testRunShrink(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->once();
            $mock->shouldReceive('height')->andReturn(100)->once();
            $mock->shouldReceive('resize')->with(80, 80)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with(20, 20, 'center', true, 'rgba(0, 0, 0, 0.5)')->andReturn($mock)->once();
        });

        $border = new Border();
        $border->setParams(['border' => '10,5000,shrink']);

        static::assertInstanceOf('Intervention\Image\Image', $border->run($image));
    }

    public function testRunExpand(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('resizeCanvas')->with(20, 20, 'center', true, 'rgba(0, 0, 0, 0.5)')->andReturn($mock)->once();
        });

        $border = new Border();
        $border->setParams(['border' => '10,5000,expand']);

        static::assertInstanceOf('Intervention\Image\Image', $border->run($image));
    }
}
