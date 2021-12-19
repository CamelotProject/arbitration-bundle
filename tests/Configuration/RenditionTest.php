<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Configuration;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Exception\InvalidRenditionParameterException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Configuration\Rendition
 *
 * @internal
 */
final class RenditionTest extends TestCase
{
    public function testGetName(): void
    {
        static::assertSame('test', (new Rendition('test', []))->getName());
    }

    public function testList(): void
    {
        $rendition = ['a' => ['z'], 'b' => ['y'], 'c' => ['x']];
        $expected = ['a', 'b', 'c'];

        static::assertSame($expected, (new Rendition('test', $rendition))->list());
    }

    public function testHas(): void
    {
        $rendition = new Rendition('test', ['a' => ['z']]);

        static::assertTrue($rendition->has('a'));
        static::assertFalse($rendition->has('z'));
    }

    public function testGet(): void
    {
        static::assertSame(['z'], (new Rendition('test', ['a' => ['z']]))->get('a'));
    }

    public function testGetInvalid(): void
    {
        $this->expectException(InvalidRenditionParameterException::class);
        $this->expectExceptionMessage('No property named "z" exists. Available names:');

        (new Rendition('test', []))->get('z');
    }

    public function testAll(): void
    {
        $rendition = ['a' => ['z'], 'b' => ['y'], 'c' => ['x']];

        static::assertSame($rendition, (new Rendition('test', $rendition))->all());
    }
}
