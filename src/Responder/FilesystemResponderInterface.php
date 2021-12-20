<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;

interface FilesystemResponderInterface extends ResponderInterface
{
    public function fileName(FileInfo $source, string|Rendition $rendition): ?string;

    public function fileNames(FileInfo $source, string $group, Renditions $renditions = null): array;
}
