<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Camelot\Arbitration\Manipulators\Helpers\Dimension;
use Intervention\Image\Image;
use League\Flysystem\FilesystemException as FilesystemV2Exception;
use League\Flysystem\FilesystemOperator;
use League\Glide\Filesystem\FilesystemException;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $dpr
 * @property string $mark
 * @property string $markfit
 * @property string $markh
 * @property string $markpad
 * @property string $markpos
 * @property string $markw
 * @property string $markx
 * @property string $marky
 * @property string $markalpha
 */
class Watermark extends BaseManipulator
{
    /**
     * The watermarks file system.
     *
     * @var null|FilesystemOperator
     */
    protected $watermarks;

    /**
     * The watermarks path prefix.
     *
     * @var string
     */
    protected $watermarksPathPrefix;

    /**
     * Create Watermark instance.
     *
     * @param FilesystemOperator $watermarks           the watermarks file system
     * @param mixed              $watermarksPathPrefix
     */
    public function __construct(FilesystemOperator $watermarks = null, $watermarksPathPrefix = '')
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
    public function getWatermarks()
    {
        return $this->watermarks;
    }

    /**
     * Set the watermarks path prefix.
     *
     * @param string $watermarksPathPrefix the watermarks path prefix
     */
    public function setWatermarksPathPrefix($watermarksPathPrefix = ''): void
    {
        $this->watermarksPathPrefix = trim($watermarksPathPrefix, '/');
    }

    /**
     * Get the watermarks path prefix.
     *
     * @return string the watermarks path prefix
     */
    public function getWatermarksPathPrefix()
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
    public function run(Image $image)
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
    public function getImage(Image $image)
    {
        if ($this->watermarks === null) {
            return;
        }

        if (!\is_string($this->mark)) {
            return;
        }

        if ($this->mark === '') {
            return;
        }

        $path = $this->mark;

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
    }

    /**
     * Get a dimension.
     *
     * @param Image  $image the source image
     * @param string $field the requested field
     *
     * @return null|float the dimension
     */
    public function getDimension(Image $image, $field)
    {
        if ($this->{$field}) {
            return (new Dimension($image, $this->getDpr()))->get($this->{$field});
        }
    }

    /**
     * Resolve the device pixel ratio.
     *
     * @return float the device pixel ratio
     */
    public function getDpr()
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
    public function getFit()
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

        if (\in_array($this->markfit, $fitMethods, true)) {
            return $this->markfit;
        }
    }

    /**
     * Get the position.
     *
     * @return string the position
     */
    public function getPosition()
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

        if (\in_array($this->markpos, $positions, true)) {
            return $this->markpos;
        }

        return 'bottom-right';
    }

    /**
     * Get the alpha channel.
     *
     * @return int the alpha
     */
    public function getAlpha()
    {
        if (!is_numeric($this->markalpha)) {
            return 100;
        }

        if ($this->markalpha < 0 || $this->markalpha > 100) {
            return 100;
        }

        return (int) $this->markalpha;
    }
}
