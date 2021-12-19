<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Contrast;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Contrast
 *
 * @internal
 */
final class ContrastTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Contrast::class, (new Contrast()));
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('contrast')->with('50')->once();
        });

        static::assertInstanceOf(Image::class, (new Contrast())->setParams(['contrast' => 50])->run($image));
    }

    public function providerContrast(): iterable
    {
        yield [50, ['contrast' => '50']];
        yield [50, ['contrast' => 50]];
        yield [null, ['contrast' => null]];
        yield [null, ['contrast' => '101']];
        yield [null, ['contrast' => '-101']];
        yield [null, ['contrast' => 'a']];
    }

    /** @dataProvider providerContrast */
    public function testGetContrast(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Contrast())->setParams($params)->getContrast());
    }
}
