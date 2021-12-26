<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Generator;

use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Generator\SourceGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Generator\SourceGenerator
 *
 * @internal
 */
final class SourceGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        static::assertSame('/render/300x200/jpg/foo/bar/image.png', $this->getSourceSet()->generate('foo/bar/image.jpg', '300x200'));
    }

    public function testGenerateInvalidPathname(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pathname missing');

        $this->getSourceSet()->generate('', '300x200');
    }

    private function getSourceSet(): SourceGenerator
    {
        return new SourceGenerator(RenditionsFixture::createFixture(), new PathnameGenerator(), '/tmp', '/tmp/render');
    }
}
