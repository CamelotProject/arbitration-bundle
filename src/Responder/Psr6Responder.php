<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use DateTimeImmutable;
use Psr\Cache\CacheItemPoolInterface;
use function is_string;

final class Psr6Responder implements ResponderInterface
{
    use ResponderTrait;

    private InterveneInterface $intervene;
    private Renditions $renditions;
    private PathnameGeneratorInterface $pathGenerator;
    private CacheItemPoolInterface $cache;

    public function __construct(InterveneInterface $intervene, Renditions $renditions, PathnameGeneratorInterface $pathGenerator, CacheItemPoolInterface $cache)
    {
        $this->intervene = $intervene;
        $this->renditions = $renditions;
        $this->pathGenerator = $pathGenerator;
        $this->cache = $cache;
    }

    public function respond(FileInfo $source, string|Rendition $rendition): ImageResponse
    {
        if (is_string($rendition)) {
            $rendition = $this->renditions->get($rendition);
        }
        $item = $this->cache->getItem($this->pathGenerator->generate($source, $rendition));
        if (!$item->isHit()) {
            $item->set($this->manipulate($source, $rendition));
            $this->cache->save($item);
        }

        $mimeType = "image/{$rendition->get('format')}";
        $lastModified = DateTimeImmutable::createFromFormat('U', (string) $source->getMTime());

        return new ImageResponse($item->get(), $mimeType, $lastModified);
    }
}
