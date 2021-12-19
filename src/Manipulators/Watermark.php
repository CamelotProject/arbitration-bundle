<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Camelot\Arbitration\Filesystem\FilesystemInterface;
use Camelot\Arbitration\Manipulators\Helpers\Dimension;
use Intervention\Image\Image;
use function in_array;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $dpr
 * @property string $watermark_path
 * @property string $watermark_fit
 * @property string $watermark_height
 * @property string $watermark_padding
 * @property string $watermark_position
 * @property string $watermark_width
 * @property string $watermark_offset_x
 * @property string $watermark_offset_y
 * @property string $watermark_alpha
 */
class Watermark extends BaseManipulator
{
    private ?FilesystemInterface $filesystem = null;

    public function setFilesystem(FilesystemInterface $filesystem): void
    {
        if ($this->filesystem) {
            throw new \RuntimeException(sprintf('Can not change %s after it has been set', FilesystemInterface::class));
        }

        $this->filesystem = $filesystem;
    }

    /**
     * Perform watermark image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        if ($watermark = $this->getImage($image)) {
            $this->insertWatermark($image, $watermark);
        }

        return $image;
    }

    /**
     * Get the watermark image.
     *
     * @param Image $image the source image
     *
     * @return null|Image the watermark image
     */
    public function getImage(Image $image): ?Image
    {
        if (!$this->filesystem) {
            return null;
        }

        $path = $this->watermark_path;
        if (!$path || !$this->filesystem->exists($path)) {
            return null;
        }

        return $image->getDriver()->init($this->filesystem->readFile($path));
    }

    /**
     * Get a dimension.
     *
     * @param Image  $image the source image
     * @param string $field the requested field
     *
     * @return null|float the dimension
     */
    public function getDimension(Image $image, string $field): ?float
    {
        if ($this->{$field}) {
            return (new Dimension($image, $this->getDpr()))->get((string) $this->{$field});
        }

        return null;
    }

    /**
     * Resolve the device pixel ratio.
     *
     * @return float the device pixel ratio
     */
    public function getDpr(): float
    {
        if (!is_numeric($this->dpr)) {
            return 1.0;
        }

        if ($this->dpr < 0 || $this->dpr > 8) {
            return 1.0;
        }

        return (float) $this->dpr;
    }

    /**
     * Get the fit.
     *
     * @return null|string the fit
     */
    public function getFit(): ?string
    {
        $fitMethods = [
            'contain',
            'max',
            'stretch',
            'crop',
            'crop-top-left',
            'crop-top',
            'crop-top-right',
            'crop-left',
            'crop-center',
            'crop-right',
            'crop-bottom-left',
            'crop-bottom',
            'crop-bottom-right',
        ];

        if (in_array($this->watermark_fit, $fitMethods, true)) {
            return $this->watermark_fit;
        }

        return null;
    }

    /**
     * Get the position.
     *
     * @return string the position
     */
    public function getPosition(): string
    {
        $positions = [
            'top-left',
            'top',
            'top-right',
            'left',
            'center',
            'right',
            'bottom-left',
            'bottom',
            'bottom-right',
        ];

        if (in_array($this->watermark_position, $positions, true)) {
            return $this->watermark_position;
        }

        return 'bottom-right';
    }

    /**
     * Get the alpha channel.
     *
     * @return int the alpha
     */
    public function getAlpha(): int
    {
        if (!is_numeric($this->watermark_alpha)) {
            return 100;
        }

        if ($this->watermark_alpha < 0 || $this->watermark_alpha > 100) {
            return 100;
        }

        return (int) $this->watermark_alpha;
    }

    private function insertWatermark(Image $image, Image $watermark): void
    {
        $offset_x = (int) $this->getDimension($image, 'watermark_offset_x');
        $offset_y = (int) $this->getDimension($image, 'watermark_offset_y');
        $padding = $this->getDimension($image, 'watermark_padding');

        if ($padding) {
            $offset_x = $offset_y = $padding;
        }

        $size = new Size();
        $size->setParams([
            'width' => $this->getDimension($image, 'watermark_width'),
            'height' => $this->getDimension($image, 'watermark_height'),
            'fit' => $this->getFit(),
        ]);
        $watermark = $size->run($watermark);

        if ($this->getAlpha() < 100) {
            $watermark->opacity($this->getAlpha());
        }

        $image->insert($watermark, $this->getPosition(), $offset_x, $offset_y);
    }
}
