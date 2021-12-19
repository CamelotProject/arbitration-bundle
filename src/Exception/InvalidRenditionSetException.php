<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Exception;

use RuntimeException;
use Throwable;
use function array_keys;
use function implode;
use function sprintf;

/** @codeCoverageIgnore  */
final class InvalidRenditionSetException extends RuntimeException
{
    public function __construct(string $name, array $set, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('No set named "%s" exists. Available names: %s', $name, implode(', ', array_keys($set)));
        parent::__construct($message, $code, $previous);
    }
}
