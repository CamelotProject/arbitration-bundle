<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Filesystem;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Filesystem\Filesystem;
use Camelot\Arbitration\Filesystem\FilesystemInterface;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function Symfony\Component\String\u;

trait FilesystemTestTrait
{
    private TemporaryDirectory $temporaryDirectory;
    private FilesystemInterface $sourceFilesystem;
    private FilesystemInterface $renderFilesystem;
    private MockObject|InterveneInterface $intervene;
    private string $sourceDir;
    private string $renderDir;

    protected function setUp(): void
    {
        $projectDir = Path::canonicalize(__DIR__ . '/../..');
        $shortName = (new ReflectionClass(__CLASS__))->getShortName();
        $location = Path::canonicalize("{$projectDir}/tmp/" . u($shortName)->snake());

        $this->temporaryDirectory = (new TemporaryDirectory($location))->create();

        $this->intervene = $this->createMock(InterveneInterface::class);
        $this->sourceDir = Path::canonicalize("{$projectDir}/tests/Fixtures");
        $this->renderDir = Path::canonicalize($this->temporaryDirectory->path() . '/render');

        $this->sourceFilesystem = new Filesystem($this->sourceDir);
        $this->renderFilesystem = new Filesystem($this->renderDir);
    }

    protected function tearDown(): void
    {
        $this->temporaryDirectory->delete();
    }

    private function mirrorSourceToRender(string $rendition): void
    {
        $generator = new PathnameGenerator();
        $rendition = RenditionsFixture::createFixture()->get($rendition);
        $fs = new SymfonyFilesystem();
        $finder = Finder::create()
            ->files()
            ->in($this->sourceDir)
            ->path('images')
        ;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $source = new FileInfo($file->getPathname(), $this->sourceDir);
            $targetPathname = Path::join($this->renderDir, $generator->generate($source, $rendition));
            $fs->copy($file->getPathname(), $targetPathname);
            $fs->touch($targetPathname, $file->getMTime());
        }
    }

    private function getTargetPathname(string $rendition): string
    {
        return Path::join($this->renderDir, (new PathnameGenerator())->generate($this->getSourceFileInfo(), RenditionsFixture::createFixture()->get($rendition)));
    }

    private function getSourceFileInfo(string $file = 'images/image.webp'): FileInfo
    {
        return new FileInfo(Path::join($this->sourceDir, $file), $this->sourceDir);
    }

    private function getRenderFileInfo(string $rendition, string $file = 'images/image.webp'): FileInfo
    {
        $relativePathname = (new PathnameGenerator())->generate($this->getSourceFileInfo($file), RenditionsFixture::createFixture()->get($rendition));

        return new FileInfo(Path::join($this->renderDir, $relativePathname), $this->renderDir);
    }
}
