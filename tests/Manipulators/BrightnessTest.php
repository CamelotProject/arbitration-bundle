<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Brightness;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Brightness
 *
 * @internal
 */
final class BrightnessTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Brightness::class, new Brightness());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('brightness')->with('50')->once();
        });

        static::assertInstanceOf(Image::class, (new Brightness())->setParams(['brightness' => 50])->run($image));
    }

    public function providerPixelate(): iterable
    {
        yield [50, ['brightness' => '50']];
        yield [50, ['brightness' => 50]];
        yield [null, ['brightness' => null]];
        yield [null, ['brightness' => '101']];
        yield [null, ['brightness' => '-101']];
        yield [null, ['brightness' => 'a']];
    }

    /** @dataProvider providerPixelate */
    public function testGetPixelate(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Brightness())->setParams($params)->getBrightness());
    }
}
