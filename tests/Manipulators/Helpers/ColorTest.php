<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators\Helpers;

use Camelot\Arbitration\Manipulators\Helpers\Color;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Intervention\Manipulators\Helpers\Color
 *
 * @internal
 */
final class ColorTest extends TestCase
{
    public function testThreeDigitColorCode(): void
    {
        $color = new Color('000');

        static::assertSame('rgba(0, 0, 0, 1)', $color->formatted());
    }

    public function testFourDigitColorCode(): void
    {
        $color = new Color('5000');

        static::assertSame('rgba(0, 0, 0, 0.5)', $color->formatted());
    }

    public function testSixDigitColorCode(): void
    {
        $color = new Color('000000');

        static::assertSame('rgba(0, 0, 0, 1)', $color->formatted());
    }

    public function testEightDigitColorCode(): void
    {
        $color = new Color('50000000');

        static::assertSame('rgba(0, 0, 0, 0.5)', $color->formatted());
    }

    public function testNamedColorCode(): void
    {
        $color = new Color('black');

        static::assertSame('rgba(0, 0, 0, 1)', $color->formatted());
    }

    public function testUnknownColor(): void
    {
        $color = new Color('unknown');

        static::assertSame('rgba(255, 255, 255, 0)', $color->formatted());
    }
}
