<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Twig;

use Camelot\Arbitration\Generator\SizesMediaQueryGenerator;
use Camelot\Arbitration\Generator\SourceGenerator;
use Camelot\Arbitration\Generator\SourceSetGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function implode;
use function sprintf;
use const PHP_EOL;

final class ArbitrationExtension extends AbstractExtension
{
    private SourceGenerator $sourceGenerator;
    private SourceSetGenerator $sourceSetGenerator;
    private SizesMediaQueryGenerator $mediaQueryGenerator;

    public function __construct(SourceGenerator $sourceGenerator, SourceSetGenerator $sourceSetGenerator, SizesMediaQueryGenerator $mediaQueryGenerator)
    {
        $this->sourceGenerator = $sourceGenerator;
        $this->sourceSetGenerator = $sourceSetGenerator;
        $this->mediaQueryGenerator = $mediaQueryGenerator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('rendition', [$this, 'getRenditionPath'], ['is_safe' => ['html']]),
            new TwigFunction('srcset', [$this, 'getSourceSet'], ['is_safe' => ['html']]),
        ];
    }

    public function getRenditionPath(string $filePathname, string $rendition): string
    {
        return $this->sourceGenerator->generate($filePathname, $rendition);
    }

    public function getSourceSet(string $filePathname, string $setName, $separator = ',' . PHP_EOL): string
    {
        $srcSet = [];
        foreach ($this->sourceSetGenerator->generate($filePathname, $setName) as $item) {
            $srcSet[] = sprintf('/%s %sw', $item['uri'], $item['width']);
        }
        $sizes = $this->mediaQueryGenerator->generate($filePathname, $setName);

        return sprintf('srcset="%s" sizes="%s"', implode($separator, $srcSet), implode($separator, $sizes));
    }
}
