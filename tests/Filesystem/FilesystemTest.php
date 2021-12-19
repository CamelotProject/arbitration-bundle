<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Filesystem;

use Camelot\Arbitration\Exception\IOException;
use Camelot\Arbitration\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Filesystem\Filesystem
 *
 * @internal
 */
final class FilesystemTest extends TestCase
{
    use FilesystemTestTrait;

    public function testGetBasePath(): void
    {
        static::assertSame($this->renderDir, $this->getFilesystem()->getBasePath());
    }

    public function testGetFileInfo(): void
    {
        $this->mirrorSourceToRender('1920x1080');

        $fileInfo = $this->getFilesystem()->getFileInfo('1920x1080/webp/images/image.webp');

        static::assertSame('1920x1080/webp/images/image.webp', $fileInfo->getRelativePathname());
    }

    public function providerExists(): iterable
    {
        $renderBase = '1920x1080/webp/images';

        yield ["{$renderBase}/image.webp"];
        yield ["{$renderBase}/aspect/landscape.webp"];
        yield ["{$renderBase}/aspect/small/landscape-small.webp"];
        yield [["{$renderBase}/image.webp", "{$renderBase}/aspect/landscape.webp", "{$renderBase}/aspect/small/landscape-small.webp"]];
    }

    /** @dataProvider providerExists */
    public function testExists(iterable|string $files): void
    {
        $this->mirrorSourceToRender('1920x1080');

        static::assertTrue($this->getFilesystem()->exists($files));
    }

    public function testNotExists(): void
    {
        static::assertFalse($this->getFilesystem()->exists('garbage'));
        static::assertFalse($this->getFilesystem()->exists(['garbage', 'index.test']));
    }

    public function testDumpFile(): void
    {
        $this->getFilesystem()->dumpFile('sub/file.test', 'updated data');

        static::assertSame('updated data', $this->getFilesystem()->readFile('sub/file.test'));
    }

    public function testReadFile(): void
    {
        $this->getFilesystem()->dumpFile('sub/file.test', 'updated data');

        static::assertSame('updated data', $this->getFilesystem()->readFile('sub/file.test'));
    }

    public function testReadFileDoesNotExist(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Failed to read file');

        $this->getFilesystem()->readFile('garbage');
    }

    public function testReadFileStream(): void
    {
        $this->mirrorSourceToRender('1920x1080');

        static::assertIsResource($this->getFilesystem()->readFileStream('1920x1080/webp/images/image.webp'));
    }

    public function testReadFileStreamDoesNotExist(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Error reading');

        $this->getFilesystem()->readFileStream('garbage');
    }

    public function providerRemove(): iterable
    {
        yield ['images/image.webp'];
        yield [['images/image.webp', 'images/aspect/landscape.webp']];
    }

    /** @dataProvider providerRemove */
    public function testRemove(string|iterable $files): void
    {
        $this->mirrorSourceToRender('1920x1080');

        $filesystem = $this->getFilesystem();
        $filesystem->remove($files);

        static::assertFalse($filesystem->exists($files));
    }

    public function testTouch(): void
    {
        $this->mirrorSourceToRender('1920x1080');

        $filesystem = $this->getFilesystem();
        $fileInfo = $filesystem->getFileInfo('1920x1080/webp/images/image.webp');

        $filesystem->touch('1920x1080/webp/images/image.webp', 42);

        static::assertSame(42, $fileInfo->getMTime());
    }

    private function getFilesystem(): Filesystem
    {
        return new Filesystem($this->renderDir);
    }
}
