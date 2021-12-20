<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Responder;

use BadMethodCallException;
use Closure;
use DateTimeInterface;
use Psr\Http\Message\StreamInterface;
use function gettype;
use function is_callable;
use function is_object;
use function is_resource;
use function is_string;
use function sprintf;

final class ImageResponse
{
    /** @var Closure|resource|string */
    private mixed $content;
    private string $mimeType;
    private DateTimeInterface $lastModified;

    public function __construct(mixed $content, string $mimeType, DateTimeInterface $lastModified)
    {
        if (!is_string($content) && !is_resource($content) && !is_callable($content) && !$content instanceof StreamInterface) {
            throw new BadMethodCallException(sprintf('Parameter #1 of %s requires either a string, callable, resource, or %s. %s given', __METHOD__, is_object($content) ? $content::class : gettype($content), StreamInterface::class));
        }
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->lastModified = $lastModified;
    }

    public function isEager(): bool
    {
        return is_string($this->content);
    }

    /** @return Closure|resource|string */
    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getLastModified(): DateTimeInterface
    {
        return $this->lastModified;
    }
}
