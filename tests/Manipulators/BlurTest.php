<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Blur;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Blur
 *
 * @internal
 */
final class BlurTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Blur::class, new Blur());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('blur')->with('10')->once();
        });

        static::assertInstanceOf(Image::class, (new Blur())->setParams(['blur' => 10])->run($image));
    }

    public function providerBlur(): iterable
    {
        yield [50, ['blur' => '50']];
        yield [50, ['blur' => 50]];
        yield [null, ['blur' => null]];
        yield [null, ['blur' => 'a']];
        yield [null, ['blur' => '-1']];
        yield [null, ['blur' => '101']];
    }

    /** @dataProvider providerBlur */
    public function testGetBlur(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Blur())->setParams($params)->getBlur());
    }
}
