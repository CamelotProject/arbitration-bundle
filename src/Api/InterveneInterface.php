<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Api;

interface InterveneInterface
{
    /**
     * Perform image manipulations.
     *
     * @param string $source source image binary data
     * @param array  $params the manipulation params
     *
     * @return string manipulated image binary data
     */
    public function handle(string $source, array $params): string;
}
