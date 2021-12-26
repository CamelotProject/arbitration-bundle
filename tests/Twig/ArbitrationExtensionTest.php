<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Twig;

use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Generator\SizesMediaQueryGenerator;
use Camelot\Arbitration\Generator\SourceGenerator;
use Camelot\Arbitration\Generator\SourceSetGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use Camelot\Arbitration\Twig\ArbitrationExtension;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Twig\TwigFunction;

/**
 * @covers \Camelot\Arbitration\Twig\ArbitrationExtension
 *
 * @internal
 */
final class ArbitrationExtensionTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $functions = $this->getExtension()->getFunctions();

        static::assertCount(2, $functions);

        static::assertInstanceOf(TwigFunction::class, $functions[0]);
        static::assertInstanceOf(TwigFunction::class, $functions[1]);

        static::assertSame('rendition', $functions[0]->getName());
        static::assertSame('srcset', $functions[1]->getName());
    }

    public function testGetRender(): void
    {
        $expected = '/render/300x200/jpg/foo/bar/image.png';
        $set = $this->getExtension()->getRenditionPath('/foo/bar/image.jpg', '300x200');

        static::assertStringContainsString($expected, $set);
    }

    public function testGetRenderSourceSet(): void
    {
        $expected = 'srcset="/render/300x200/jpg/foo/bar/image.png 300w,
/render/150x100/jpg/foo/bar/image.png 150w"';
        $set = $this->getExtension()->getSourceSet('foo/bar/image.jpg', 'list_page');

        static::assertStringContainsString($expected, $set);
    }

    public function testGetRenderSourceSetSeparator(): void
    {
        $expected = 'srcset="/render/300x200/jpg/foo/bar/image.png 300w, /render/150x100/jpg/foo/bar/image.png 150w"';
        $set = $this->getExtension()->getSourceSet('foo/bar/image.jpg', 'list_page', ', ');

        static::assertStringContainsString($expected, $set);
    }

    public function testGetRenderSourceSetNoPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pathname missing');

        $this->getExtension()->getSourceSet('', 'list_page', ', ');
    }

    private function getExtension(): ArbitrationExtension
    {
        $renditions = RenditionsFixture::createFixture();
        $sourceGenerator = new SourceGenerator($renditions, new PathnameGenerator(), '/tmp', '/tmp/render');
        $sourceSetGenerator = new SourceSetGenerator($renditions, new PathnameGenerator(), '/tmp', '/tmp/render');
        $mediaQueryGenerator = new SizesMediaQueryGenerator($renditions);

        return new ArbitrationExtension($sourceGenerator, $sourceSetGenerator, $mediaQueryGenerator);
    }
}
