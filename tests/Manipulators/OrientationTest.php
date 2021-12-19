<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Orientation;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Orientation
 *
 * @internal
 */
final class OrientationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Orientation::class, new Orientation());
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('orientate')->andReturn($mock)->once();
            $mock->shouldReceive('rotate')->andReturn($mock)->with('90')->once();
        });

        static::assertInstanceOf(Image::class, (new Orientation())->setParams(['orientation' => 'auto'])->run($image));

        static::assertInstanceOf(Image::class, (new Orientation())->setParams(['orientation' => '90'])->run($image));
    }

    public function providerOrientation(): iterable
    {
        yield ['auto', ['orientation' => 'auto']];
        yield [0, ['orientation' => '0']];
        yield [90, ['orientation' => '90']];
        yield [180, ['orientation' => '180']];
        yield [270, ['orientation' => '270']];
        yield ['auto', ['orientation' => null]];
        yield ['auto', ['orientation' => '1']];
        yield ['auto', ['orientation' => '45']];
    }

    /** @dataProvider providerOrientation */
    public function testGetOrientation(int|string $expected, array $params): void
    {
        static::assertSame($expected, (new Orientation())->setParams($params)->getOrientation());
    }
}
