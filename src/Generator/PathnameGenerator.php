<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Generator;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Filesystem\FileInfo;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use function array_shift;
use function count;
use function explode;
use function sprintf;
use const DIRECTORY_SEPARATOR;

/**
 * Builds & resolves path names between source & render images.
 *
 * e.g. images/blog/cat.gif --> 1920x1080/gif/images/blog/cat.webp
 */
final class PathnameGenerator implements PathnameGeneratorInterface
{
    public function base(Rendition $rendition): string
    {
        return $rendition->getName();
    }

    public function generate(FileInfo $source, Rendition $rendition): string
    {
        return sprintf(
            '%s/%s/%s/%s.%s',
            $rendition->getName(),
            $source->getExtension(),
            $source->getRelativePath(),
            $source->getFilenameWithoutExtension(),
            $rendition->get('format')
        );
    }

    public function resolve(FileInfo $render): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $render->getRelativePath());
        if (count($parts) < 3) {
            throw new RuntimeException(sprintf('Invalid render path %s. Did you pass the source path instead?', $render->getRelativePath()));
        }

        // Remove Rendition name segment
        array_shift($parts);
        $originalExtension = array_shift($parts);
        $relativePath = Path::join(...$parts);

        return sprintf(
            '%s/%s.%s',
            $relativePath,
            $render->getFilenameWithoutExtension(),
            $originalExtension
        );
    }

    public function resolveRendition(FileInfo $source): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $source->getRelativePath());

        return array_shift($parts);
    }
}
