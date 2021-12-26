<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Message;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Filesystem\FileInfo;

trait ImageRenderTrait
{
    private FileInfo $fileInfo;
    private iterable $renditions;

    public function __construct(FileInfo $fileInfo, iterable $renditions)
    {
        $this->fileInfo = $fileInfo;
        $this->renditions = $renditions;
    }

    public function getFileInfo(): FileInfo
    {
        return $this->fileInfo;
    }

    /** @return Rendition[] */
    public function getRenditions(): iterable
    {
        return $this->renditions;
    }
}
