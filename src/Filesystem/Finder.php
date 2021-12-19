<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Filesystem;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Finder\SplFileInfo;

final class Finder
{
    private FilesystemInterface $imagesFilesystem;
    private FilesystemInterface $renderFilesystem;
    private PathnameGeneratorInterface $pathname;

    public function __construct(FilesystemInterface $imagesFilesystem, FilesystemInterface $renderFilesystem, PathnameGeneratorInterface $pathname)
    {
        $this->imagesFilesystem = $imagesFilesystem;
        $this->renderFilesystem = $renderFilesystem;
        $this->pathname = $pathname;
    }

    public function findSourceFiles(null|string|array $paths, string|array $exclude = []): iterable
    {
        return $this->find($this->imagesFilesystem, $paths, $exclude);
    }

    public function findRenderFiles(null|string|array $paths, string|array $exclude = []): iterable
    {
        return $this->find($this->renderFilesystem, $paths, $exclude);
    }

    public function getSourceFromRender(FileInfo $render): FileInfo
    {
        return $this->imagesFilesystem->getFileInfo($this->pathname->resolve($render));
    }

    public function getRenditionNameFromRender(FileInfo $render): string
    {
        return $this->pathname->resolveRendition($render);
    }

    public function getRenderFromSource(FileInfo $source, Rendition $rendition): FileInfo
    {
        return $this->renderFilesystem->getFileInfo($this->pathname->generate($source, $rendition));
    }

    private function find(FilesystemInterface $filesystem, null|string|array $paths, string|array $exclude = []): iterable
    {
        $files = SymfonyFinder::create()
            ->files()
            ->in($filesystem->getBasePath())
            ->path((array) $paths)
            ->notPath($exclude)
            ->name('/\.(avif|bmp|gif|png|jp?g|webp)$/')
        ;
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            yield new FileInfo($file->getPathname(), $filesystem->getBasePath());
        }
    }
}
