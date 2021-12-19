<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Configuration;

use Camelot\Arbitration\Configuration\Set;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Configuration\Set
 *
 * @internal
 */
final class SetTest extends TestCase
{
    public function testGetMediaQueries(): void
    {
        $expected = [1, 2, 3];

        static::assertSame($expected, (new Set([], $expected))->getMediaQueries());
    }

    public function testGetRenditions(): void
    {
        $expected = [1, 2, 3];

        static::assertSame($expected, (new Set($expected, []))->getRenditions());
    }
}
