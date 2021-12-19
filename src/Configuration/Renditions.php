<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Configuration;

use Camelot\Arbitration\Exception\InvalidRenditionException;
use Camelot\Arbitration\Exception\InvalidRenditionSetException;
use function array_key_exists;
use function array_keys;

final class Renditions
{
    private array $renditions;
    private array $sets;

    public function __construct(array $renditions, array $sets = [])
    {
        $this->renditions = $renditions;
        $this->sets = $sets;
    }

    public function list(): array
    {
        return array_keys($this->renditions);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->renditions);
    }

    public function get(string $name): Rendition
    {
        if (!array_key_exists($name, $this->renditions)) {
            throw new InvalidRenditionException($name, $this->renditions);
        }

        if (!$this->renditions[$name] instanceof Rendition) {
            $this->renditions[$name] = new Rendition($name, $this->renditions[$name]);
        }

        return $this->renditions[$name];
    }

    public function listSets(): array
    {
        return array_keys($this->sets);
    }

    public function hasSet(string $name): bool
    {
        return array_key_exists($name, $this->sets);
    }

    public function getSet(string $name): Set
    {
        $set = $this->sets[$name] ?? false;
        if ($set === false) {
            throw new InvalidRenditionSetException($name, $this->sets);
        }

        if ($set instanceof Set) {
            return $set;
        }

        $renditions = [];
        foreach ($set['renditions'] as $renditionName) {
            $renditions[$renditionName] = $this->get($renditionName);
        }
        $this->sets[$name] = new Set($renditions, $set['media_queries'] ?? []);

        return $this->sets[$name];
    }
}
