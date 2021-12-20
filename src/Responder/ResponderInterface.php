<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;

interface ResponderInterface
{
    public function respond(FileInfo $source, string|Rendition $rendition): ImageResponse;

    /** @return ImageResponse[] */
    public function respondBatch(FileInfo $source, string $group, Renditions $renditions = null): iterable;
}
