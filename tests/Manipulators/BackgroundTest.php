<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Background;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
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
        static::assertInstanceOf('League\Glide\Manipulators\Background', new Background());
    }

    public function testRun(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->once();
            $mock->shouldReceive('height')->andReturn(100)->once();
            $mock->shouldReceive('getDriver')->andReturn(Mockery::mock('Intervention\Image\AbstractDriver', function ($mock): void {
                $mock->shouldReceive('newImage')->with(100, 100, 'rgba(0, 0, 0, 1)')->andReturn(Mockery::mock('Intervention\Image\Image', function ($mock): void {
                    $mock->shouldReceive('insert')->andReturn($mock)->once();
                }))->once();
            }))->once();
        });

        $border = new Background();

        static::assertInstanceOf('Intervention\Image\Image', $border->run($image));
        static::assertInstanceOf('Intervention\Image\Image', $border->setParams(['bg' => 'black'])->run($image));
    }
}
