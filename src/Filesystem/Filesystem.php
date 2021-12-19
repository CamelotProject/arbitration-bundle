<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Filesystem;

use Camelot\Arbitration\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Path;
use function file_get_contents;
use function is_string;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

final class Filesystem implements FilesystemInterface, StreamableFilesystemInterface
{
    private string $basePath;
    private SymfonyFilesystem $decorated;

    public function __construct(string $basePath, SymfonyFilesystem $decorated = null)
    {
        $this->basePath = $basePath;
        $this->decorated = $decorated ?: new SymfonyFilesystem();
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getFileInfo(string $path): FileInfo
    {
        return new FileInfo(Path::join($this->basePath, $path), $this->basePath);
    }

    public function exists(iterable|string $files): bool
    {
        return $this->decorated->exists($this->prefixPath($files));
    }

    public function readFile(string $filepath): string
    {
        set_error_handler(function (int $type, string $msg) use (&$error): void { $error = $msg; });
        $filepath = $this->prefixPath($filepath);

        try {
            $contents = file_get_contents($filepath);
        } finally {
            restore_error_handler();
        }

        if ($contents === false) {
            throw new IOException(sprintf('Failed to read file: "%s": ', $filepath) . $error);
        }

        return $contents;
    }

    /** @return resource */
    public function readFileStream(string $filepath)
    {
        $filepath = $this->prefixPath($filepath);

        error_clear_last();
        $resource = @fopen($filepath, 'r');

        if ($resource === false) {
            throw new IOException(sprintf('Error reading %s. %s', $filepath, error_get_last()['message'] ?? ''));
        }

        return $resource;
    }

    public function dumpFile(string $filename, $content): void
    {
        $this->decorated->dumpFile($this->prefixPath($filename), $content);
    }

    public function remove(string|iterable $files): void
    {
        $this->decorated->remove($this->prefixPath($files));
    }

    public function touch(string|iterable $files, int $time = null, int $atime = null): void
    {
        $this->decorated->touch($this->prefixPath($files), $time, $atime);
    }

    private function prefixPath(string|iterable $files): string|iterable
    {
        if (is_string($files)) {
            return Path::join($this->basePath, $files);
        }

        $paths = [];
        foreach ($files as $file) {
            $paths[] = Path::join($this->basePath, $file);
        }

        return $paths;
    }
}
