<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Filesystem;

use Camelot\Arbitration\Filesystem\Finder;
use Camelot\Arbitration\Filesystem\Supervisor;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Responder\FilesystemResponder;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * @covers \Camelot\Arbitration\Filesystem\Supervisor
 *
 * @internal
 */
final class SupervisorTest extends TestCase
{
    use FilesystemTestTrait;

    private ?LoggerInterface $logger = null;

    public function testVerify(): void
    {
        $this->mirrorSourceToRender('1920x1080');

        $this->getSupervisor()->verify('images/image.webp', false);

        static::assertSame($this->sourceFilesystem->readFile('images/image.webp'), $this->renderFilesystem->readFile('1920x1080/webp/images/image.webp'));
    }

    public function testVerifyFileMismatch(): void
    {
        $expect = $this->sourceFilesystem->readFile('images/image.webp');

        $this->mirrorSourceToRender('1920x1080');
        $this->renderFilesystem->dumpFile('1920x1080/webp/images/image.webp', 'abc');
        $this->intervene
            ->expects(static::once())
            ->method('handle')
            ->willReturn($expect)
        ;

        $this->getSupervisor()->verify('images/image.webp', false);

        static::assertSame($expect, $this->renderFilesystem->readFile('1920x1080/webp/images/image.webp'));
    }

    public function testVerifyMissingSource(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->logger
            ->expects(static::once())
            ->method('warning')
            ->with('[ORPHAN] 1920x1080/webp/images/has-no-source.webp is orphaned')
        ;

        $this->mirrorSourceToRender('1920x1080');
        $this->renderFilesystem->dumpFile('1920x1080/webp/images/has-no-source.webp', 'abc');

        $this->getSupervisor()->verify('images/has-no-source.webp', false);
    }

    public function testVerifyMissingSourceRemoveRendered(): void
    {
        $this->mirrorSourceToRender('1920x1080');
        $this->renderFilesystem->dumpFile('1920x1080/webp/images/has-no-source.webp', 'abc');

        static::assertTrue($this->renderFilesystem->exists('1920x1080/webp/images/has-no-source.webp'));

        $this->getSupervisor()->verify('images/has-no-source.webp', true);

        static::assertFalse($this->renderFilesystem->exists('1920x1080/webp/images/has-no-source.webp'));
    }

    public function testVerifyInvalidRenditionName(): void
    {
        $this->mirrorSourceToRender('1920x1080');
        $this->renderFilesystem->dumpFile('1920x1080/webp/images/image.webp', 'abc');

        $fs = new Filesystem();
        $basePath = $this->renderFilesystem->getBasePath();
        $fs->rename("$basePath/1920x1080", "$basePath/0x0");

        static::assertTrue($this->renderFilesystem->exists('0x0/webp/images/image.webp'), 'Test set-up failed');

        $this->getSupervisor()->verify('images/image.webp', true);

        static::assertFalse($this->renderFilesystem->exists('0x0/webp/images/image.webp'));
    }

    public function providerPrimeFile(): iterable
    {
        $renditions = RenditionsFixture::createFixture();

        yield [['1024x768/webp/images/image.webp', '1920x1080/webp/images/image.webp'], 'images/image.webp', $renditions->getSet('page')->getRenditions()];
        yield [['150x100/webp/images/image.png', '300x200/webp/images/image.png'], 'images/image.webp', $renditions->getSet('list_page')->getRenditions()];
    }

    /** @dataProvider providerPrimeFile */
    public function testPrimeFile(array $expected, string $path, iterable $renditions): void
    {
        $this->getSupervisor()->primeFile($path, $renditions);

        foreach ($expected as $expect) {
            static::assertFileExists(Path::join($this->renderDir, $expect));
        }
    }

    public function providerExpireFile(): iterable
    {
        yield [false];
        yield [true];
    }

    /** @dataProvider providerExpireFile */
    public function testExpireFile(bool $render): void
    {
        $base1920 = Path::join($this->renderDir, '1920x1080', 'webp');
        $base300 = Path::join($this->renderDir, '300x200', 'webp');

        $portrait = 'images/aspect/small/portrait-small';
        $landscape = 'images/aspect/small/landscape-small';

        $this->mirrorSourceToRender('1920x1080');
        $this->mirrorSourceToRender('300x200');
        $this->validateExpireMirror($base1920, $base300, $portrait, $landscape);

        $this->getSupervisor()->expireFile("{$portrait}.webp", $render);

        static::assertFileExists(Path::join($base1920, "{$landscape}.webp"));
        static::assertFileExists(Path::join($base300, "{$landscape}.png"));

        if ($render) {
            static::assertFileExists(Path::join($base1920, "{$portrait}.webp"));
            static::assertFileExists(Path::join($base300, "{$portrait}.png"));
        } else {
            static::assertFileDoesNotExist(Path::join($base1920, "{$portrait}.webp"));
            static::assertFileDoesNotExist(Path::join($base300, "{$portrait}.png"));
        }
    }

    public function providerPrimeSet(): iterable
    {
        yield [['150x100/webp/images/image.png', '300x200/webp/images/image.png'], 'list_page', 'images/image.webp'];
        yield [['1024x768/webp/images/image.webp', '1920x1080/webp/images/image.webp'], 'page', 'images/image.webp'];
    }

    /** @dataProvider providerPrimeSet */
    public function testPrimeSet(mixed $expected, string $set, string|array $paths): void
    {
        $this->getSupervisor()->primeSet($set, $paths);

        foreach ($expected as $expect) {
            static::assertFileExists(Path::join($this->renderDir, $expect));
        }
    }

    public function testExpireSet(): void
    {
        $renditions = RenditionsFixture::createFixture()->getSet('page')->getRenditions();
        foreach ($renditions as $rendition) {
            $this->mirrorSourceToRender($rendition->getName());

            static::assertDirectoryExists(Path::join($this->renderDir, $rendition->getName()));
        }

        $this->getSupervisor()->expireSet('page');

        foreach ($renditions as $rendition) {
            static::assertDirectoryDoesNotExist(Path::join($this->renderDir, $rendition->getName()));
        }
    }

    public function providerPrimeRendition(): iterable
    {
        yield [
            ['1920x1080/webp/images/aspect/small/landscape-small.webp', '1920x1080/webp/images/aspect/small/portrait-small.webp'],
            '1920x1080',
            'images/aspect/small',
        ];
        yield [
            ['1024x768/webp/images/aspect/small/landscape-small.webp', '1024x768/webp/images/aspect/small/portrait-small.webp'],
            '1024x768',
            'images/aspect/small',
        ];
    }

    /** @dataProvider providerPrimeRendition */
    public function testPrimeRendition(mixed $expected, string $rendition, string|array $paths): void
    {
        $this->getSupervisor()->primeRendition($rendition, $paths);

        foreach ($expected as $expect) {
            static::assertFileExists(Path::join($this->renderDir, $expect));
        }
    }

    public function testExpireRendition(): void
    {
        $this->mirrorSourceToRender('1920x1080');
        static::assertDirectoryExists(Path::join($this->renderDir, '1920x1080'));

        $this->getSupervisor()->expireRendition('1920x1080');

        static::assertDirectoryDoesNotExist(Path::join($this->renderDir, '1920x1080'));
    }

    private function validateExpireMirror(string $base1920, string $base300, string $portrait, string $landscape): void
    {
        $paths = [
            Path::join($base1920, "{$portrait}.webp"),
            Path::join($base1920, "{$landscape}.webp"),
            Path::join($base300, "{$portrait}.png"),
            Path::join($base300, "{$landscape}.png"),
        ];
        $fs = new Filesystem();
        $valid = true;
        foreach ($paths as $path) {
            if ($fs->exists($path)) {
                continue;
            }
            $valid = false;
        }
        if (!$valid) {
            $this->fail('Test files did not set up correctly!');
        }
    }

    private function getSupervisor(): Supervisor
    {
        return new Supervisor(
            $this->sourceFilesystem,
            $this->renderFilesystem,
            new FilesystemResponder($this->intervene, RenditionsFixture::createFixture(), $this->renderFilesystem, new PathnameGenerator()),
            new PathnameGenerator(),
            new Finder($this->sourceFilesystem, $this->renderFilesystem, new PathnameGenerator()),
            RenditionsFixture::createFixture(),
            $this->logger,
        );
    }
}
