<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use DateTimeImmutable;
use Psr\SimpleCache\CacheInterface;
use function is_string;

final class Psr16Responder implements ResponderInterface
{
    use ResponderTrait;

    private InterveneInterface $intervene;
    private Renditions $renditions;
    private PathnameGeneratorInterface $pathGenerator;
    private CacheInterface $cache;

    public function __construct(InterveneInterface $intervene, Renditions $renditions, PathnameGeneratorInterface $pathGenerator, CacheInterface $cache)
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
        $key = $this->pathGenerator->generate($source, $rendition);
        $content = $this->cache->get($key);
        if (!$content) {
            $content = $this->manipulate($source, $rendition);
            $this->cache->set($key, $content);
        }

        $mimeType = "image/{$rendition->get('format')}";
        $lastModified = DateTimeImmutable::createFromFormat('U', (string) $source->getMTime());

        return new ImageResponse($content, $mimeType, $lastModified);
    }
}
