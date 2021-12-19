<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Camelot\Arbitration\Manipulators\Helpers\Dimension;
use Intervention\Image\Image;
use League\Flysystem\FilesystemException as FilesystemV2Exception;
use League\Flysystem\FilesystemOperator;
use League\Glide\Filesystem\FilesystemException;
use function in_array;
use function is_string;

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
    /** The watermarks file system. */
    protected ?FilesystemOperator $watermarks;
    /** The watermarks path prefix. */
    protected string $watermarksPathPrefix;

    /**
     * Create Watermark instance.
     *
     * @param FilesystemOperator $watermarks           the watermarks file system
     * @param mixed|string       $watermarksPathPrefix
     */
    public function __construct(FilesystemOperator $watermarks = null, string $watermarksPathPrefix = '')
    {
        $this->setWatermarks($watermarks);
        $this->setWatermarksPathPrefix($watermarksPathPrefix);
    }

    /**
     * Set the watermarks file system.
     *
     * @param FilesystemOperator $watermarks the watermarks file system
     */
    public function setWatermarks(FilesystemOperator $watermarks = null): void
    {
        $this->watermarks = $watermarks;
    }

    /**
     * Get the watermarks file system.
     *
     * @return null|FilesystemOperator the watermarks file system
     */
    public function getWatermarks(): ?FilesystemOperator
    {
        return $this->watermarks;
    }

    /**
     * Set the watermarks path prefix.
     *
     * @param string $watermarksPathPrefix the watermarks path prefix
     */
    public function setWatermarksPathPrefix(string $watermarksPathPrefix = ''): void
    {
        $this->watermarksPathPrefix = trim($watermarksPathPrefix, '/');
    }

    /**
     * Get the watermarks path prefix.
     *
     * @return string the watermarks path prefix
     */
    public function getWatermarksPathPrefix(): string
    {
        return $this->watermarksPathPrefix;
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
            $markw = $this->getDimension($image, 'markw');
            $markh = $this->getDimension($image, 'markh');
            $markx = $this->getDimension($image, 'markx');
            $marky = $this->getDimension($image, 'marky');
            $markpad = $this->getDimension($image, 'markpad');
            $markfit = $this->getFit();
            $markpos = $this->getPosition();
            $markalpha = $this->getAlpha();

            if ($markpad) {
                $markx = $marky = $markpad;
            }

            $size = new Size();
            $size->setParams([
                'w' => $markw,
                'h' => $markh,
                'fit' => $markfit,
            ]);
            $watermark = $size->run($watermark);

            if ($markalpha < 100) {
                $watermark->opacity($markalpha);
            }

            $image->insert($watermark, $markpos, (int) $markx, (int) $marky);
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
        if ($this->watermarks === null) {
            return null;
        }

        if (!is_string($this->watermark_path)) {
            return null;
        }

        if ($this->watermark_path === '') {
            return null;
        }

        $path = $this->watermark_path;

        if ($this->watermarksPathPrefix) {
            $path = $this->watermarksPathPrefix . '/' . $path;
        }

        try {
            if ($this->watermarks->fileExists($path)) {
                $source = $this->watermarks->read($path);

                return $image->getDriver()->init($source);
            }
        } catch (FilesystemV2Exception $exception) {
            throw new FilesystemException('Could not read the image `' . $path . '`.');
        }

        return null;
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
}
