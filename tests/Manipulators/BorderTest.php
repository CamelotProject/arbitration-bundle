<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Border;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Border
 *
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
        static::assertInstanceOf(Border::class, new Border());
    }

    public function testGetBorder(): void
    {
        $image = Mockery::mock(Image::class);
        $border = new Border();

        static::assertNull($border->getBorder($image));

        static::assertSame(
            [10.0, 'rgba(0, 0, 0, 1)', 'overlay'],
            $border->setParams(['border' => '10,black'])->getBorder($image)
        );
    }

    public function testGetInvalidBorder(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertNull((new Border())->setParams(['border' => '0,black'])->getBorder($image));
    }

    public function testGetWidth(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertSame(100.0, (new Border())->getWidth($image, 1, '100'));
    }

    public function testGetColor(): void
    {
        static::assertSame('rgba(0, 0, 0, 1)', (new Border())->getColor('black'));
    }

    public function providerMethod(): iterable
    {
        yield ['expand', 'expand'];
        yield ['shrink', 'shrink'];
        yield ['overlay', 'overlay'];
        yield ['overlay', 'invalid'];
    }

    /** @dataProvider providerMethod */
    public function testGetMethod(string $expected, string $method): void
    {
        static::assertSame($expected, (new Border())->getMethod($method));
    }

    public function providerDpr(): iterable
    {
        yield [1.0, ['dpr' => 'invalid']];
        yield [1.0, ['dpr' => '-1']];
        yield [1.0, ['dpr' => '9']];
        yield [2.0, ['dpr' => '2']];
    }

    /** @dataProvider providerDpr */
    public function testGetDpr(float $expected, array $params): void
    {
        static::assertSame($expected, (new Border())->setParams($params)->getDpr());
    }

    public function testRunWithNoBorder(): void
    {
        $image = Mockery::mock(Image::class);

        static::assertInstanceOf(Image::class, (new Border())->run($image));
    }

    public function testRunOverlay(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->once();
            $mock->shouldReceive('height')->andReturn(100)->once();
            $mock->shouldReceive('rectangle')->with(5, 5, 95, 95, Mockery::on(fn ($closure) => true))->andReturn($mock)->once();
        });

        $border = new Border();
        $border->setParams(['border' => '10,5000,overlay']);

        static::assertInstanceOf(Image::class, $border->run($image));
    }

    public function testRunShrink(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100)->once();
            $mock->shouldReceive('height')->andReturn(100)->once();
            $mock->shouldReceive('resize')->with(80, 80)->andReturn($mock)->once();
            $mock->shouldReceive('resizeCanvas')->with(20, 20, 'center', true, 'rgba(0, 0, 0, 0.5)')->andReturn($mock)->once();
        });

        $border = new Border();
        $border->setParams(['border' => '10,5000,shrink']);

        static::assertInstanceOf(Image::class, $border->run($image));
    }

    public function testRunExpand(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('resizeCanvas')->with(20, 20, 'center', true, 'rgba(0, 0, 0, 0.5)')->andReturn($mock)->once();
        });

        $border = new Border();
        $border->setParams(['border' => '10,5000,expand']);

        static::assertInstanceOf(Image::class, $border->run($image));
    }
}
