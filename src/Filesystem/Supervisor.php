<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Filesystem;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Exception\InvalidRenditionException;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Responder\FilesystemResponder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function is_string;

final class Supervisor
{
    private Filesystem $imagesFilesystem;
    private Filesystem $renderFilesystem;
    private FilesystemResponder $responder;
    private PathnameGenerator $generator;
    private Finder $finder;
    private Renditions $renditions;
    private LoggerInterface $logger;

    public function __construct(Filesystem $imagesFilesystem, Filesystem $renderFilesystem, FilesystemResponder $responder, PathnameGenerator $generator, Finder $finder, Renditions $renditions, LoggerInterface $logger = null)
    {
        $this->imagesFilesystem = $imagesFilesystem;
        $this->renderFilesystem = $renderFilesystem;
        $this->responder = $responder;
        $this->generator = $generator;
        $this->finder = $finder;
        $this->renditions = $renditions;
        $this->logger = $logger ?: new NullLogger();
    }

    public function verify(?string $path, bool $remove): void
    {
        foreach ($this->finder->findRenderFiles($path) as $file) {
            $render = $this->renderFilesystem->getFileInfo($file->getRelativePathname());
            $relativePath = $this->generator->resolve($render);
            if (!$this->imagesFilesystem->exists($relativePath)) {
                $renderPath = $file->getRelativePathname();
                if ($remove) {
                    $this->logger->notice("[DELETE] Removing orphaned file {$renderPath}");
                    $this->renderFilesystem->remove($renderPath);
                } else {
                    $this->logger->warning("[ORPHAN] {$renderPath} is orphaned");
                }

                continue;
            }

            $this->doVerify($render, $relativePath, $remove);
        }
    }

    public function primeFile(string $path, iterable $renditions): void
    {
        $source = $this->imagesFilesystem->getFileInfo($path);

        foreach ($renditions as $rendition) {
            $rendition = is_string($rendition) ? $this->renditions->get($rendition) : $rendition;
            $renderPath = $this->generator->generate($source, $rendition);
            $render = $this->renderFilesystem->getFileInfo($renderPath);
            if (!$this->renderFilesystem->exists($renderPath) || $source->getMTime() !== $render->getMTime()) {
                $this->logger->info("[RENDER] Priming {$rendition->getName()} render files for source file: {$renderPath}");
                $this->responder->respond($source, $rendition);
            }
        }
    }

    public function expireFile(string $path, bool $replace): void
    {
        $source = $this->imagesFilesystem->getFileInfo($path);

        foreach ($this->renditions->list() as $rendition) {
            $renderPath = $this->generator->generate($source, $this->renditions->get($rendition));
            if ($this->renderFilesystem->exists($renderPath)) {
                $this->logger->notice("[DELETE] Removing expired render file {$renderPath}");
                $this->renderFilesystem->remove($renderPath);
                if ($replace) {
                    $this->logger->info("[RENDER] Re-rendering expired file {$renderPath}");
                    $this->responder->respond($source, $rendition);
                }
            }
        }
    }

    public function primeSet(string $set, string|array $paths): void
    {
        /** @var FileInfo $source */
        $this->logger->info("Priming {$set} render set");

        foreach ($this->finder->findSourceFiles($paths) as $source) {
            $renditions = $this->renditions->getSet($set)->getRenditions();
            foreach ($renditions as $rendition) {
                $this->primeRendition($rendition, $paths);
            }
        }
    }

    public function expireSet(string $set): void
    {
        $this->logger->info("Removing expired render set: {$set}");
        $renditions = $this->renditions->getSet($set)->getRenditions();
        foreach ($renditions as $rendition) {
            $this->expireRendition($rendition->getName());
        }
    }

    public function primeRendition(string|Rendition $rendition, string|array $paths): void
    {
        $rendition = is_string($rendition) ? $this->renditions->get($rendition) : $rendition;

        /** @var FileInfo $source */
        foreach ($this->finder->findSourceFiles($paths) as $source) {
            $renderPath = $this->generator->generate($source, $rendition);
            $render = $this->renderFilesystem->getFileInfo($renderPath);
            if (!$this->renderFilesystem->exists($renderPath) || $source->getMTime() !== $render->getMTime()) {
                $this->logger->notice("[RENDER] Rendering {$rendition} for file: {$source->getRelativePathname()}");
                $this->responder->respond($source, $rendition);
            } else {
                $this->logger->info("[OK] Nothing needed for {$source->getRelativePathname()}");
            }
        }
    }

    public function expireRendition(string $rendition): void
    {
        $this->logger->notice("[DELETE] Removing expired rendition files: {$rendition}");
        $this->renderFilesystem->remove($this->generator->base($this->renditions->get($rendition)));
    }

    private function doVerify(FileInfo $render, string $relativePath, bool $remove): void
    {
        $source = $this->imagesFilesystem->getFileInfo($relativePath);
        if ($source->getMTime() === $render->getMTime()) {
            $this->logger->info("[OK] {$render->getRelativePathname()}");

            return;
        }

        $this->logger->warning("[INVALID] File modification time mismatch. Regenerating: {$render->getRelativePathname()}");
        $rendition = $this->generator->resolveRendition($render);

        try {
            $this->responder->respond($source, $rendition);
        } catch (InvalidRenditionException $e) {
            $this->logger->error("[ERROR] {$e->getMessage()}");

            if ($remove) {
                $this->logger->notice("[DELETE] Removing invalid rendition {$rendition}");
                $this->renderFilesystem->remove($rendition);
            }
        }
    }
}
