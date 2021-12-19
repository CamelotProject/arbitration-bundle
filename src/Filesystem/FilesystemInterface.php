<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Filesystem;

use Camelot\Arbitration\Exception\IOException;

interface FilesystemInterface
{
    /** Returns the base path in use by the filesystem */
    public function getBasePath(): string;

    /** Returns a FileInfo object for a filepath on the managed filesystem */
    public function getFileInfo(string $path): FileInfo;

    /** Checks the existence of files or directories. */
    public function exists(string|iterable $files): bool;

    /**
     * Reads contents of an existing file.
     *
     * @throws IOException If the file is not readable
     */
    public function readFile(string $filepath): string;

    /**
     * Atomically dumps content into a file.
     *
     * @param resource|string $content The data to write into the file
     *
     * @throws IOException if the file cannot be written to
     */
    public function dumpFile(string $filename, mixed $content): void;

    /**
     * Removes files or directories.
     *
     * @throws IOException When removal fails
     */
    public function remove(string|iterable $files): void;

    /**
     * Sets access and modification time of file.
     *
     * @param null|int $time  The touch time as a Unix timestamp, if not supplied the current system time is used
     * @param null|int $atime The access time as a Unix timestamp, if not supplied the current system time is used
     */
    public function touch(string|iterable $files, int $time = null, int $atime = null): void;
}
