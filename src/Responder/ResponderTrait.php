<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;
use DateTimeImmutable;
use function is_string;

trait ResponderTrait
{
    private InterveneInterface $intervene;
    private Renditions $renditions;

    public function __construct(InterveneInterface $intervene, Renditions $renditions)
    {
        $this->intervene = $intervene;
        $this->renditions = $renditions;
    }

    public function respond(FileInfo $source, string|Rendition $rendition): ImageResponse
    {
        $rendition = is_string($rendition) ? $this->renditions->get($rendition) : $rendition;
        $mimeType = "image/{$rendition->get('format')}";
        $lastModified = DateTimeImmutable::createFromFormat('U', (string) $source->getMTime());

        return new ImageResponse($this->manipulate($source, $rendition), $mimeType, $lastModified);
    }

    public function respondBatch(FileInfo $source, string $group, Renditions $renditions = null): iterable
    {
        $renditions = $renditions ?: $this->renditions;
        foreach ($renditions->getSet($group)->getRenditions() as $rendition) {
            yield $rendition->getName() => $this->respond($source, $rendition);
        }
    }

    private function manipulate(FileInfo $source, Rendition $rendition): string
    {
        return $this->intervene->handle($source->getContents(), $rendition->all());
    }
}
