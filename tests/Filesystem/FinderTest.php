<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Filesystem;

use Camelot\Arbitration\Filesystem\Finder;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

/**
 * @covers \Camelot\Arbitration\Filesystem\Finder
 *
 * @internal
 */
final class FinderTest extends TestCase
{
    use FilesystemTestTrait;

    public function providerFindSourceFiles(): iterable
    {
        yield [5, 'images'];
        yield [4, 'images/aspect'];
        yield [2, 'images/aspect/small'];
    }

    /** @dataProvider providerFindSourceFiles */
    public function testFindSourceFiles(int $expected, string|array $paths): void
    {
        $finder = $this->getFinder();
        $files = $finder->findSourceFiles($paths);

        static::assertCount($expected, iterator_to_array($files));
    }

    /** @dataProvider providerFindSourceFiles */
    public function testFindRenderFiles(int $expected, string|array $paths): void
    {
        $this->mirrorSourceToRender('1920x1080');

        $finder = $this->getFinder();
        $files = $finder->findRenderFiles($paths);

        static::assertCount($expected, iterator_to_array($files));
    }

    public function testGetSourceFromRender(): void
    {
        static::assertSame('images/image.webp', $this->getFinder()->getSourceFromRender($this->getRenderFileInfo('1920x1080'))->getRelativePathname());
    }

    public function testGetRenditionNameFromRender(): void
    {
        static::assertSame('1920x1080', $this->getFinder()->getRenditionNameFromRender($this->getRenderFileInfo('1920x1080')));
    }

    public function testGetRenderFromSource(): void
    {
        static::assertSame('1920x1080/webp/images/image.webp', $this->getFinder()->getRenderFromSource($this->getSourceFileInfo(), RenditionsFixture::createFixture()->get('1920x1080'))->getRelativePathname());
    }

    private function getFinder(): Finder
    {
        return new Finder($this->sourceFilesystem, $this->renderFilesystem, new PathnameGenerator());
    }
}
