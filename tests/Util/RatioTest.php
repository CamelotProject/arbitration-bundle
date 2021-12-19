<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Util;

use Camelot\Arbitration\Util\Ratio;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Util\Ratio
 *
 * @internal
 */
final class RatioTest extends TestCase
{
    public function providerNormaliseOptions(): iterable
    {
        yield 'Null returns null' => [null, null];
        yield 'Ratio only' => [
            ['ratio' => 1.5, 'width' => null, 'height' => null],
            ['ratio' => '1.5'],
        ];
        yield 'Width only' => [
            ['width' => 150, 'ratio' => null, 'height' => null],
            ['width' => '150px'],
        ];
        yield 'Height Only' => [
            ['height' => 100, 'ratio' => null, 'width' => null],
            ['height' => 100],
        ];
        yield 'Ratio & width only' => [
            ['ratio' => 1.5, 'width' => 150, 'height' => 100],
            ['ratio' => 1.5, 'width' => 150, 'height' => null],
        ];
        yield 'Ration & height only' => [
            ['ratio' => 1.5, 'height' => 100, 'width' => 150],
            ['ratio' => 1.5, 'height' => 100],
        ];
        yield 'All' => [
            ['ratio' => 1.5, 'width' => 150, 'height' => 100],
            ['ratio' => 1.5, 'width' => 150, 'height' => 100],
        ];
    }

    /** @dataProvider providerNormaliseOptions */
    public function testNormaliseOptions(?array $expected, ?array $options): void
    {
        static::assertSame($expected, Ratio::normaliseOptions($options));
    }
}
