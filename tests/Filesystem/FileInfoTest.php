<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Filesystem;

use Camelot\Arbitration\Exception\IOException;
use Camelot\Arbitration\Filesystem\FileInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use function dirname;
use function file_put_contents;
use function filemtime;
use function serialize;
use function unserialize;

/**
 * @covers \Camelot\Arbitration\Filesystem\FileInfo
 *
 * @internal
 */
final class FileInfoTest extends TestCase
{
    use FilesystemTestTrait;

    public function testSerializtion(): void
    {
        $fileInfo = $this->getSourceFileInfo();
        $newFileInfo = unserialize(serialize($fileInfo));

        static::assertSame('images', $newFileInfo->getRelativePath());
        static::assertSame('images/image.webp', $newFileInfo->getRelativePathname());
    }

    public function testGetPath(): void
    {
        static::assertSame(Path::join($this->sourceDir, 'images'), $this->getSourceFileInfo()->getPath());
    }

    public function testGetPathname(): void
    {
        static::assertSame(Path::join($this->sourceDir, 'images/image.webp'), $this->getSourceFileInfo()->getPathname());
    }

    public function testGetFilename(): void
    {
        static::assertSame('image.webp', $this->getSourceFileInfo()->getFilename());
    }

    public function testGetExtension(): void
    {
        static::assertSame('webp', $this->getSourceFileInfo()->getExtension());
    }

    public function testGetFilenameWithoutExtension(): void
    {
        static::assertSame('file', (new FileInfo('/tmp/file.test', '/tmp', ''))->getFilenameWithoutExtension());
    }

    public function providerPaths(): iterable
    {
        yield ['image.jpg', '/var/www/public/image.jpg', '/var/www/public'];
        yield ['images/blog/image.jpg', '/var/www/public/images/blog/image.jpg', '/var/www/public'];
        yield ['blog/image.jpg', '/var/www/public/images/blog/image.jpg', '/var/www/public/images'];
        yield ['image.jpg', '/var/www/public/images/blog/image.jpg', '/var/www/public/images/blog'];
    }

    /** @dataProvider providerPaths */
    public function testGetRelativePathname(string $expected, string $filePath, string $basePath): void
    {
        static::assertSame($expected, (new FileInfo($filePath, $basePath, ''))->getRelativePathname());
    }

    /** @dataProvider providerPaths */
    public function testGetRelativePath(string $expected, string $filePath, string $basePath): void
    {
        static::assertSame(dirname($expected), (new FileInfo($filePath, $basePath, ''))->getRelativePath());
    }

    public function testGetContents(): void
    {
        $filepath = Path::join($this->renderDir, 'index.test');
        mkdir(dirname($filepath));
        file_put_contents($filepath, 'test index');

        static::assertSame('test index', (new FileInfo($filepath, $this->renderDir, ''))->getContents());
    }

    public function testGetContentsError(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Failed to read file');

        (new FileInfo(Path::join($this->renderDir, 'garbage'), $this->renderDir, ''))->getContents();
    }

    public function testGetMTime(): void
    {
        static::assertSame(filemtime(Path::join($this->sourceDir, 'images/image.webp')), $this->getSourceFileInfo()->getMTime());
    }
}
