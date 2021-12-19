<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Generator;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Camelot\Arbitration\Generator\PathnameGenerator
 *
 * @internal
 */
final class PathnameGeneratorTest extends TestCase
{
    public function testBase(): void
    {
        static::assertSame('1920x1080', (new PathnameGenerator())->base(RenditionsFixture::createFixture()->get('1920x1080')));
    }

    public function providerGenerate(): iterable
    {
        $source = new FileInfo('/var/www/images/blog/cat.jpg', '/var/www/images');
        $renditions = RenditionsFixture::createFixture();

        yield ['1920x1080/jpg/blog/cat.webp', $source, $renditions->get('1920x1080')];
        yield ['1024x768/jpg/blog/cat.webp', $source, $renditions->get('1024x768')];
        yield ['150x100/jpg/blog/cat.png', $source, $renditions->get('150x100')];
        yield ['150x100/jpg/blog/cat.png', $source, $renditions->get('150x100')];
    }

    /** @dataProvider providerGenerate */
    public function testGenerate(string $expected, FileInfo $source, Rendition $rendition): void
    {
        static::assertSame($expected, (new PathnameGenerator())->generate($source, $rendition));
    }

    public function providerResolve(): iterable
    {
        yield ['blog/cat.gif', '1920x1080/gif/blog/cat.webp'];
        yield ['blog/cat.gif', '1024x768/gif/blog/cat.webp'];
        yield ['blog/cat.jpg', '150x100/jpg/blog/cat.png'];
        yield ['blog/cat.jpg', '150x100/jpg/blog/cat.png'];
    }

    /** @dataProvider providerResolve */
    public function testResolve(string $expected, string $source): void
    {
        $source = new FileInfo('/var/www/render/' . $source, '/var/www/render/');

        static::assertSame($expected, (new PathnameGenerator())->resolve($source));
    }

    public function testResolveInvalidPath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid render path');

        (new PathnameGenerator())->resolve(new FileInfo('/invalid/', '/'));
    }

    public function testResolveRendition(): void
    {
        $source = new FileInfo('/var/www/images/blog/cat.jpg', '/var/www/images');

        static::assertSame('blog', (new PathnameGenerator())->resolveRendition($source));
    }
}
