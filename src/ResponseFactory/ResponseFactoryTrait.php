<?php

declare(strict_types=1);

namespace Camelot\Arbitration\ResponseFactory;

use DateTimeInterface;
use function date_create_immutable;

trait ResponseFactoryTrait
{
    private int $maxAge;

    public function __construct(int $maxAge)
    {
        $this->maxAge = $maxAge;
    }

    private function getMaxAge(): int
    {
        return $this->maxAge;
    }

    private function getExpires(): DateTimeInterface
    {
        return date_create_immutable()->modify("+{$this->maxAge} seconds");
    }
}
