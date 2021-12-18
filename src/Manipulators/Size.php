<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;
use function array_key_exists;
use function in_array;
use function is_numeric;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string      $dpr
 * @property null|string $fit
 * @property string      $h
 * @property string      $w
 */
class Size extends BaseManipulator
{
    /** Maximum image size in pixels. */
    protected ?int $maxImageSize;

    /**
     * Create Size instance.
     *
     * @param null|int $maxImageSize maximum image size in pixels
     */
    public function __construct(int $maxImageSize = null)
    {
        $this->maxImageSize = $maxImageSize;
    }

    /**
     * Set the maximum image size.
     *
     * @param null|int maximum image size in pixels
     */
    public function setMaxImageSize(mixed $maxImageSize): void
    {
        $this->maxImageSize = $maxImageSize;
    }

    /**
     * Get the maximum image size.
     *
     * @return null|int maximum image size in pixels
     */
    public function getMaxImageSize(): ?int
    {
        return $this->maxImageSize;
    }

    /**
     * Perform size image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $fit = $this->getFit();
        $dpr = $this->getDpr();

        [$width, $height] = $this->resolveMissingDimensions($image, $width, $height);
        [$width, $height] = $this->applyDpr($width, $height, $dpr);
        [$width, $height] = $this->limitImageSize($width, $height);

        if ($width !== $image->width() || $height !== $image->height()) {
            $image = $this->runResize($image, $fit, $width, $height);
        }

        return $image;
    }

    /**
     * Resolve width.
     *
     * @return null|int the resolved width
     */
    public function getWidth(): ?int
    {
        if (!is_numeric($this->w)) {
            return null;
        }

        if ($this->w <= 0) {
            return null;
        }

        return (int) $this->w;
    }

    /**
     * Resolve height.
     *
     * @return null|int the resolved height
     */
    public function getHeight(): ?int
    {
        if (!is_numeric($this->h)) {
            return null;
        }

        if ($this->h <= 0) {
            return null;
        }

        return (int) $this->h;
    }

    /**
     * Resolve fit.
     *
     * @return string the resolved fit
     */
    public function getFit(): ?string
    {
        if ($this->fit === null) {
            return 'contain';
        }

        if (in_array($this->fit, ['contain', 'fill', 'max', 'stretch'], true)) {
            return $this->fit;
        }

        if (preg_match('/^(crop)(-top-left|-top|-top-right|-left|-center|-right|-bottom-left|-bottom|-bottom-right|-[\d]{1,3}-[\d]{1,3}(?:-[\d]{1,3}(?:\.\d+)?)?)*$/', $this->fit)) {
            return 'crop';
        }

        return 'contain';
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
     * Resolve missing image dimensions.
     *
     * @param Image    $image  the source image
     * @param null|int $width  the image width
     * @param null|int $height the image height
     *
     * @return int[] the resolved width and height
     */
    public function resolveMissingDimensions(Image $image, ?int $width, ?int $height): array
    {
        if ($width === null && $height === null) {
            $width = $image->width();
            $height = $image->height();
        }

        if ($width === null || $height === null) {
            $size = (new \Intervention\Image\Size($image->width(), $image->height()))
                ->resize($width, $height, function ($constraint): void {
                    $constraint->aspectRatio();
                })
            ;

            $width = $size->getWidth();
            $height = $size->getHeight();
        }

        return [$width, $height];
    }

    /**
     * Apply the device pixel ratio.
     *
     * @param int   $width  the target image width
     * @param int   $height the target image height
     * @param float $dpr    the device pixel ratio
     *
     * @return int[] the modified width and height
     */
    public function applyDpr(int $width, int $height, float $dpr): array
    {
        $width = $width * $dpr;
        $height = $height * $dpr;

        return [
            (int) round($width),
            (int) round($height),
        ];
    }

    /**
     * Limit image size to maximum allowed image size.
     *
     * @param int $width  the image width
     * @param int $height the image height
     *
     * @return int[] the limited width and height
     */
    public function limitImageSize(int $width, int $height): array
    {
        if ($this->maxImageSize !== null) {
            $imageSize = $width * $height;

            if ($imageSize > $this->maxImageSize) {
                $width = $width / sqrt($imageSize / $this->maxImageSize);
                $height = $height / sqrt($imageSize / $this->maxImageSize);
            }
        }

        return [
            (int) $width,
            (int) $height,
        ];
    }

    /**
     * Perform resize image manipulation.
     *
     * @param Image  $image  the source image
     * @param string $fit    the fit
     * @param int    $width  the width
     * @param int    $height the height
     *
     * @return Image the manipulated image
     */
    public function runResize(Image $image, string $fit, int $width, int $height): Image
    {
        if ($fit === 'contain') {
            return $this->runContainResize($image, $width, $height);
        }

        if ($fit === 'fill') {
            return $this->runFillResize($image, $width, $height);
        }

        if ($fit === 'max') {
            return $this->runMaxResize($image, $width, $height);
        }

        if ($fit === 'stretch') {
            return $this->runStretchResize($image, $width, $height);
        }

        if ($fit === 'crop') {
            return $this->runCropResize($image, $width, $height);
        }

        return $image;
    }

    /**
     * Perform contain resize image manipulation.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return Image the manipulated image
     */
    public function runContainResize(Image $image, int $width, int $height): Image
    {
        return $image->resize($width, $height, function ($constraint): void {
            $constraint->aspectRatio();
        });
    }

    /**
     * Perform max resize image manipulation.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return Image the manipulated image
     */
    public function runMaxResize(Image $image, int $width, int $height): Image
    {
        return $image->resize($width, $height, function ($constraint): void {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    /**
     * Perform fill resize image manipulation.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return Image the manipulated image
     */
    public function runFillResize(Image $image, int $width, int $height): Image
    {
        $image = $this->runMaxResize($image, $width, $height);

        return $image->resizeCanvas($width, $height, 'center');
    }

    /**
     * Perform stretch resize image manipulation.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return Image the manipulated image
     */
    public function runStretchResize(Image $image, int $width, int $height): Image
    {
        return $image->resize($width, $height);
    }

    /**
     * Perform crop resize image manipulation.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return Image the manipulated image
     */
    public function runCropResize(Image $image, int $width, int $height): Image
    {
        [$resize_width, $resize_height] = $this->resolveCropResizeDimensions($image, $width, $height);

        $zoom = $this->getCrop()[2];

        $image->resize($resize_width * $zoom, $resize_height * $zoom, function ($constraint): void {
            $constraint->aspectRatio();
        });

        [$offset_x, $offset_y] = $this->resolveCropOffset($image, $width, $height);

        return $image->crop($width, $height, $offset_x, $offset_y);
    }

    /**
     * Resolve the crop resize dimensions.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return array the resize dimensions
     */
    public function resolveCropResizeDimensions(Image $image, int $width, int $height): array
    {
        if ($height > $width * ($image->height() / $image->width())) {
            return [$height * ($image->width() / $image->height()), $height];
        }

        return [$width, $width * ($image->height() / $image->width())];
    }

    /**
     * Resolve the crop offset.
     *
     * @param Image $image  the source image
     * @param int   $width  the width
     * @param int   $height the height
     *
     * @return array the crop offset
     */
    public function resolveCropOffset(Image $image, int $width, int $height): array
    {
        [$offset_percentage_x, $offset_percentage_y] = $this->getCrop();

        $offset_x = (int) (($image->width() * $offset_percentage_x / 100) - ($width / 2));
        $offset_y = (int) (($image->height() * $offset_percentage_y / 100) - ($height / 2));

        $max_offset_x = $image->width() - $width;
        $max_offset_y = $image->height() - $height;

        if ($offset_x < 0) {
            $offset_x = 0;
        }

        if ($offset_y < 0) {
            $offset_y = 0;
        }

        if ($offset_x > $max_offset_x) {
            $offset_x = $max_offset_x;
        }

        if ($offset_y > $max_offset_y) {
            $offset_y = $max_offset_y;
        }

        return [$offset_x, $offset_y];
    }

    /**
     * Resolve crop with zoom.
     *
     * @return (float|int)[] The resolved crop
     *
     * @psalm-return array{0: int, 1: int, 2: float}
     */
    public function getCrop(): array
    {
        $cropMethods = [
            'crop-top-left' => [0, 0, 1.0],
            'crop-top' => [50, 0, 1.0],
            'crop-top-right' => [100, 0, 1.0],
            'crop-left' => [0, 50, 1.0],
            'crop-center' => [50, 50, 1.0],
            'crop-right' => [100, 50, 1.0],
            'crop-bottom-left' => [0, 100, 1.0],
            'crop-bottom' => [50, 100, 1.0],
            'crop-bottom-right' => [100, 100, 1.0],
        ];

        if ($this->fit === null) {
            return [50, 50, 1.0];
        }

        if (array_key_exists($this->fit, $cropMethods)) {
            return $cropMethods[$this->fit];
        }

        if (preg_match('/^crop-([\d]{1,3})-([\d]{1,3})(?:-([\d]{1,3}(?:\.\d+)?))*$/', $this->fit, $matches)) {
            $matches[3] ??= 1;

            if ($matches[1] > 100 || $matches[2] > 100 || $matches[3] > 100) {
                return [50, 50, 1.0];
            }

            return [
                (int) $matches[1],
                (int) $matches[2],
                (float) $matches[3],
            ];
        }

        return [50, 50, 1.0];
    }
}
