<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Generator;

use Camelot\Arbitration\Configuration\Renditions;
use RuntimeException;

final class SizesMediaQueryGenerator
{
    private Renditions $renditions;

    public function __construct(Renditions $renditions)
    {
        $this->renditions = $renditions;
    }

    public function generate(string $pathname, string $setName): array
    {
        if (!$pathname) {
            throw new RuntimeException('Pathname missing.');
        }

        return $this->renditions->getSet($setName)->getMediaQueries();
    }
}
