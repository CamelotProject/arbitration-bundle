<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Background;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\Background
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 *
 * @internal
 */
final class BackgroundTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Background::class, new Background());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->once();
            $mock->shouldReceive('height')->andReturn(100)->once();
            $mock->shouldReceive('getDriver')->andReturn(Mockery::mock(AbstractDriver::class, function ($mock): void {
                $mock->shouldReceive('newImage')->with(100, 100, 'rgba(0, 0, 0, 1)')->andReturn(Mockery::mock(Image::class, function ($mock): void {
                    $mock->shouldReceive('insert')->andReturn($mock)->once();
                }))->once();
            }))->once();
        });

        $border = new Background();

        static::assertInstanceOf(Image::class, $border->run($image));
        static::assertInstanceOf(Image::class, $border->setParams(['bg' => 'black'])->run($image));
    }
}
