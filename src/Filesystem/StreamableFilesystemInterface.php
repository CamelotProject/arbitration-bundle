<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Filesystem;

interface StreamableFilesystemInterface
{
    /** @return resource */
    public function readFileStream(string $filepath);
}
