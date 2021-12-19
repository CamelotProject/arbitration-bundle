<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Flip;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Flip
 *
 * @internal
 */
final class FlipTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Flip::class, new Flip());
    }

    public function providerRun(): iterable
    {
        yield [[], null];
        yield [['h'], 'h'];
        yield [['v'], 'v'];
        yield [['h', 'v'], 'both'];
    }

    /** @dataProvider providerRun */
    public function testRun(array $expected, ?string $direction): void
    {
        $image = Mockery::mock(Image::class, function ($mock) use ($expected): void {
            foreach ($expected as $expect) {
                $mock->shouldReceive('flip')->andReturn($mock)->with($expect)->once();
            }
        });

        static::assertInstanceOf(Image::class, (new Flip())->setParams(['flip' => $direction])->run($image));
    }

    public function providerFlip(): iterable
    {
        yield ['h', ['flip' => 'h']];
        yield ['v', ['flip' => 'v']];
        yield ['both', ['flip' => 'both']];
    }

    /** @dataProvider providerFlip */
    public function testGetFlip(?string $expected, array $params): void
    {
        static::assertSame($expected, (new Flip())->setParams($params)->getFlip());
    }
}
