<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Filesystem\FilesystemInterface;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use DateTimeImmutable;
use function is_string;

final class FilesystemResponder implements FilesystemResponderInterface
{
    use ResponderTrait;

    private InterveneInterface $intervene;
    private Renditions $renditions;
    private FilesystemInterface $filesystem;
    private PathnameGeneratorInterface $pathGenerator;

    public function __construct(InterveneInterface $intervene, Renditions $renditions, FilesystemInterface $renderFilesystem, PathnameGeneratorInterface $pathGenerator)
    {
        $this->intervene = $intervene;
        $this->renditions = $renditions;
        $this->filesystem = $renderFilesystem;
        $this->pathGenerator = $pathGenerator;
    }

    public function respond(FileInfo $source, string|Rendition $rendition): ImageResponse
    {
        if (is_string($rendition)) {
            $rendition = $this->renditions->get($rendition);
        }
        $cachePathname = $this->pathGenerator->generate($source, $rendition);

        $this->dumpFile($source, $rendition, $cachePathname);

        $mimeType = "image/{$rendition->get('format')}";
        $lastModified = DateTimeImmutable::createFromFormat('U', (string) $source->getMTime());

        return new ImageResponse($this->filesystem->readFile($cachePathname), $mimeType, $lastModified);
    }

    public function fileName(FileInfo $source, string|Rendition $rendition): string
    {
        if (is_string($rendition)) {
            $rendition = $this->renditions->get($rendition);
        }

        return $this->pathGenerator->generate($source, $rendition);
    }

    public function fileNames(FileInfo $source, string $group, Renditions $renditions = null): array
    {
        $fileNames = [];
        $renditions = $renditions ?: $this->renditions;
        foreach ($renditions->getSet($group)->getRenditions() as $rendition) {
            $fileNames[$rendition->getName()] = $this->fileName($source, $rendition);
        }

        return $fileNames;
    }

    /** Create modified image if is does not exist, or if source images is newer than the render, repeat the render */
    private function dumpFile(FileInfo $source, Rendition $rendition, string $cachePathname): void
    {
        if ($this->filesystem->exists($cachePathname) && $source->getMTime() === $this->filesystem->getFileInfo($cachePathname)->getMTime()) {
            return;
        }

        $this->filesystem->dumpFile($cachePathname, $this->manipulate($source, $rendition));
        $this->filesystem->touch($cachePathname, $source->getMTime());
    }
}
