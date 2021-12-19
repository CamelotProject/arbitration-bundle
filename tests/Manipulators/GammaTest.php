<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Gamma;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Gamma
 *
 * @internal
 */
final class GammaTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Gamma::class, new Gamma());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('gamma')->with('1.5')->once();
        });

        static::assertInstanceOf(Image::class, (new Gamma())->setParams(['gamma' => '1.5'])->run($image));
    }

    public function providerGamma(): iterable
    {
        yield [1.5, ['gamma' => '1.5']];
        yield [1.5, ['gamma' => 1.5]];
        yield [null, ['gamma' => null]];
        yield [null, ['gamma' => 'a']];
        yield [null, ['gamma' => '.1']];
        yield [null, ['gamma' => '9.999']];
        yield [null, ['gamma' => '0.005']];
        yield [null, ['gamma' => '-1']];
    }

    /** @dataProvider providerGamma */
    public function testGetGamma(?float $expected, array $params): void
    {
        static::assertSame($expected, (new Gamma())->setParams($params)->getGamma());
    }
}
