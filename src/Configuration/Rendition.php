<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Configuration;

use Camelot\Arbitration\Exception\InvalidRenditionParameterException;
use function array_key_exists;
use function array_keys;

final class Rendition
{
    private string $name;
    private array $config;

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function list(): array
    {
        return array_keys($this->config);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->config);
    }

    public function get(string $name): null|float|int|string|array
    {
        if (!array_key_exists($name, $this->config)) {
            throw new InvalidRenditionParameterException($name, $this->config);
        }

        return $this->config[$name];
    }

    /** @return Rendition[] */
    public function all(): array
    {
        return $this->config;
    }
}
