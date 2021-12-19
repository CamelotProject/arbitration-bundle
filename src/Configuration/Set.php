<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Configuration;

/**
 * @internal
 */
final class Set
{
    private array $mediaQueries;
    private array $renditions;

    public function __construct(array $renditions, array $mediaQueries)
    {
        $this->renditions = $renditions;
        $this->mediaQueries = $mediaQueries;
    }

    public function getMediaQueries(): array
    {
        return $this->mediaQueries;
    }

    /** @return Rendition[] */
    public function getRenditions(): array
    {
        return $this->renditions;
    }
}
