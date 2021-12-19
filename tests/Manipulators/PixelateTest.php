<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Pixelate;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Pixelate
 *
 * @internal
 */
final class PixelateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Pixelate::class, new Pixelate());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('pixelate')->with('10')->once();
        });

        static::assertInstanceOf(Image::class, (new Pixelate())->setParams(['pixelate' => '10'])->run($image));
    }

    public function providerPixelate(): iterable
    {
        yield [50, ['pixelate' => '50']];
        yield [50, ['pixelate' => 50.50]];
        yield [null, ['pixelate' => null]];
        yield [null, ['pixelate' => 'a']];
        yield [null, ['pixelate' => '-1']];
        yield [null, ['pixelate' => '1001']];
    }

    /** @dataProvider providerPixelate */
    public function testGetPixelate(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Pixelate())->setParams($params)->getPixelate());
    }
}
