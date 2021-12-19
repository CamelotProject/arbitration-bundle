<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Filesystem;

use Camelot\Arbitration\Exception\IOException;
use SplFileInfo;
use Stringable;
use Symfony\Component\Filesystem\Path;
use function array_diff;
use function dirname;
use function explode;
use function file_get_contents;
use function pathinfo;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use const DIRECTORY_SEPARATOR;

/**
 * Describes a file in terms of relative paths.
 */
final class FileInfo
{
    private SplFileInfo $fileInfo;
    private string $filePathname;
    private string $relativePath;
    private string $relativePathname;

    public function __construct(string|Stringable $filePathname, string $basePath)
    {
        $this->filePathname = (string) $filePathname;
        $this->relativePathname = Path::makeRelative($filePathname, $basePath);
        $this->relativePath = dirname($this->relativePathname);
        $this->fileInfo = new SplFileInfo($filePathname);
    }

    public function __serialize(): array
    {
        return [
            'filePathname' => $this->filePathname,
            'relativePathname' => $this->relativePathname,
        ];
    }

    public function __unserialize(array $data): void
    {
        $basePath = Path::join(DIRECTORY_SEPARATOR, ...array_diff(explode('/', Path::canonicalize($data['filePathname'])), explode('/', Path::canonicalize($data['relativePathname']))));

        $this->__construct($data['filePathname'], $basePath);
    }

    public function getPath(): string
    {
        return $this->getSplFileInfo()->getPath();
    }

    public function getFilename(): string
    {
        return $this->getSplFileInfo()->getFilename();
    }

    public function getExtension(): string
    {
        return $this->getSplFileInfo()->getExtension();
    }

    public function getPathname(): string
    {
        return $this->getSplFileInfo()->getPathname();
    }

    public function getFilenameWithoutExtension(): string
    {
        $filename = $this->filePathname;

        return pathinfo($filename, PATHINFO_FILENAME);
    }

    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }

    /**
     * Reads contents of an the file.
     *
     * @throws IOException If the file is not readable
     */
    public function getContents(): string
    {
        set_error_handler(function (int $type, string $msg) use (&$error): void { $error = $msg; });

        try {
            $content = file_get_contents($this->filePathname);
        } finally {
            restore_error_handler();
        }

        if ($content === false) {
            throw new IOException(sprintf('Failed to read file: "%s": ', $this->filePathname) . $error);
        }

        return $content;
    }

    /** Gets the last modified time. */
    public function getMTime(): int
    {
        return $this->getSplFileInfo()->getMTime();
    }

    private function getSplFileInfo(): SplFileInfo
    {
        return $this->fileInfo;
    }
}
