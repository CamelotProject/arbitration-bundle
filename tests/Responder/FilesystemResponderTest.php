<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Responder;

use Camelot\Arbitration\Filesystem\FilesystemInterface;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Responder\FilesystemResponder;
use Camelot\Arbitration\Tests\Filesystem\FilesystemTestTrait;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use function filemtime;
use function iterator_to_array;

/**
 * @covers \Camelot\Arbitration\Responder\FilesystemResponder
 *
 * @internal
 */
final class FilesystemResponderTest extends TestCase
{
    use FilesystemTestTrait;

    public function providerRespond(): iterable
    {
        yield 'Uncached' => [false, '1920x1080'];
        yield 'Cached' => [true, '1920x1080'];
    }

    /** @dataProvider providerRespond */
    public function testRespond(bool $isCached, string $rendition): void
    {
        if ($isCached === false) {
            $this->intervene
                ->expects(static::once())
                ->method('handle')
                ->willReturn('handled goods')
            ;
        }

        $targetPathname = $this->getTargetPathname($rendition);
        static::assertFileDoesNotExist($targetPathname);

        $this->getResponder()->respond($this->getSourceFileInfo(), $rendition);

        static::assertFileExists($targetPathname);
        static::assertSame($this->getSourceFileInfo()->getMTime(), filemtime($this->getTargetPathname($rendition)));
    }

    public function testRespondExistingMatchingRender()
    {
        $rendition = '1920x1080';
        $this->mirrorSourceToRender($rendition);

        $render = $this->getRenderFileInfo('1920x1080');

        $renderFilesystem = $this->createMock(FilesystemInterface::class);
        $renderFilesystem
            ->expects(static::once())
            ->method('exists')
            ->willReturn(true)
        ;
        $renderFilesystem
            ->expects(static::once())
            ->method('getFileInfo')
            ->willReturn($render)
        ;

        $responder = new FilesystemResponder($this->intervene, RenditionsFixture::createFixture(), $renderFilesystem, new PathnameGenerator());
        $responder->respond($this->getSourceFileInfo(), $rendition);
    }

    public function testRespondSourceIsNewerThanRender(): void
    {
        $rendition = '1920x1080';
        $this->mirrorSourceToRender($rendition);

        $targetPathname = $this->getTargetPathname($rendition);
        $fs = new SymfonyFilesystem();
        $fs->dumpFile($targetPathname, 'asdf');
        $fs->touch($targetPathname, 42);

        $this->getResponder()->respond($this->getSourceFileInfo(), $rendition);

        static::assertSame($this->getSourceFileInfo()->getMTime(), filemtime($targetPathname));
    }

    public function providerRespondBatch(): iterable
    {
        yield 'Uncached' => [false];
        yield 'Cached' => [true];
    }

    /** @dataProvider providerRespondBatch */
    public function testRespondBatch(bool $isCached): void
    {
        if ($isCached === false) {
            $this->intervene
                ->expects(static::atLeast(2))
                ->method('handle')
                ->willReturn('handled goods')
            ;
        }

        $response = $this->getResponder()->respondBatch($this->getSourceFileInfo(), 'list_page');
        iterator_to_array($response);

        foreach (RenditionsFixture::createFixture()->getSet('list_page')->getRenditions() as $rendition) {
            $targetPathname = $this->getTargetPathname($rendition->getName());

            static::assertFileExists($targetPathname);
            static::assertSame($this->getSourceFileInfo()->getMTime(), filemtime($this->getTargetPathname($rendition->getName())));
        }
    }

    public function testFileName(): void
    {
        static::assertSame('1920x1080/webp/images/image.webp', $this->getResponder()->fileName($this->getSourceFileInfo(), '1920x1080'));
    }

    public function testFileNames(): void
    {
        $response = $this->getResponder()->fileNames($this->getSourceFileInfo(), 'list_page');

        static::assertCount(2, $response);
        static::assertSame('300x200/webp/images/image.png', $response['300x200']);
        static::assertSame('150x100/webp/images/image.png', $response['150x100']);
    }

    private function getResponder(): FilesystemResponder
    {
        return new FilesystemResponder(
            $this->intervene,
            RenditionsFixture::createFixture(),
            $this->renderFilesystem,
            new PathnameGenerator(),
        );
    }
}
