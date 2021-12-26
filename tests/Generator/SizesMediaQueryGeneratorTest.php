<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Generator;

use Camelot\Arbitration\Generator\SizesMediaQueryGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Generator\SizesMediaQueryGenerator
 *
 * @internal
 */
final class SizesMediaQueryGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new SizesMediaQueryGenerator(RenditionsFixture::createFixture());

        static::assertSame(['(max-width: 768px) 1024px', '(min-width: 768px) 1920px'], $generator->generate('path/image.jpg', 'page'));
    }

    public function testGenerateInvalidPathname(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pathname missing');

        (new SizesMediaQueryGenerator(RenditionsFixture::createFixture()))->generate('', 'page');
    }
}
