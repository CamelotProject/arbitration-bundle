<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Generator;

use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\Filesystem\FileInfo;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

final class SourceSetGenerator
{
    private Renditions $renditions;
    private PathnameGeneratorInterface $pathname;
    private string $imagesPath;
    private string $renderPath;

    /**
     * @param string $imagesPath base path of the source images
     * @param string $renderPath base path of the rendered images
     */
    public function __construct(Renditions $renditions, PathnameGeneratorInterface $pathname, string $imagesPath, string $renderPath)
    {
        $this->renditions = $renditions;
        $this->pathname = $pathname;
        $this->imagesPath = $imagesPath;
        $this->renderPath = $renderPath;
    }

    public function generate(string $pathname, string $setName): array
    {
        if (!$pathname) {
            throw new RuntimeException('Pathname missing.');
        }

        $renderPath = Path::makeRelative($this->renderPath, $this->imagesPath);
        $srcSet = [];
        foreach ($this->renditions->getSet($setName)->getRenditions() as $rendition) {
            $srcSet[$rendition->getName()] = [
                'width' => $rendition->get('width'),
                'height' => $rendition->get('height'),
                'uri' => Path::join($renderPath, $this->pathname->generate(new FileInfo(Path::join($this->imagesPath, $pathname), $this->imagesPath), $rendition)),
            ];
        }

        return $srcSet;
    }
}
