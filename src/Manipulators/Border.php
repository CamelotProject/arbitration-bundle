<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Camelot\Arbitration\Manipulators\Helpers\Color;
use Camelot\Arbitration\Manipulators\Helpers\Dimension;
use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $border
 * @property string $dpr
 */
class Border extends BaseManipulator
{
    /**
     * Perform border image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        if ($border = $this->getBorder($image)) {
            [$width, $color, $method] = $border;

            if ($method === 'overlay') {
                return $this->runOverlay($image, $width, $color);
            }

            if ($method === 'shrink') {
                return $this->runShrink($image, $width, $color);
            }

            if ($method === 'expand') {
                return $this->runExpand($image, $width, $color);
            }
        }

        return $image;
    }

    /**
     * Resolve border amount.
     *
     * @param Image $image the source image
     *
     * @return (float|string)[]|null The resolved border amount
     *
     * @psalm-return array{0: float, 1: string, 2: string}|null
     */
    public function getBorder(Image $image)
    {
        if (!$this->border) {
            return;
        }

        $values = explode(',', $this->border);

        $width = $this->getWidth($image, $this->getDpr(), $values[0] ?? null);
        $color = $this->getColor($values[1] ?? null);
        $method = $this->getMethod($values[2] ?? null);

        if ($width) {
            return [$width, $color, $method];
        }
    }

    /**
     * Get border width.
     *
     * @param Image  $image the source image
     * @param float  $dpr   the device pixel ratio
     * @param string $width the border width
     *
     * @return null|float the resolved border width
     */
    public function getWidth(Image $image, $dpr, $width)
    {
        return (new Dimension($image, $dpr))->get($width);
    }

    /**
     * Get formatted color.
     *
     * @param string $color the color
     *
     * @return string the formatted color
     */
    public function getColor($color)
    {
        return (new Color($color))->formatted();
    }

    /**
     * Resolve the border method.
     *
     * @param string $method the raw border method
     *
     * @return string the resolved border method
     */
    public function getMethod($method)
    {
        if (!\in_array($method, ['expand', 'shrink', 'overlay'], true)) {
            return 'overlay';
        }

        return $method;
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
     * Run the overlay border method.
     *
     * @param Image  $image the source image
     * @param float  $width the border width
     * @param string $color the border color
     *
     * @return Image the manipulated image
     */
    public function runOverlay(Image $image, $width, $color)
    {
        return $image->rectangle(
            (int) round($width / 2),
            (int) round($width / 2),
            (int) round($image->width() - ($width / 2)),
            (int) round($image->height() - ($width / 2)),
            function ($draw) use ($width, $color): void {
                $draw->border($width, $color);
            }
        );
    }

    /**
     * Run the shrink border method.
     *
     * @param Image  $image the source image
     * @param float  $width the border width
     * @param string $color the border color
     *
     * @return Image the manipulated image
     */
    public function runShrink(Image $image, $width, $color)
    {
        return $image
            ->resize(
                (int) round($image->width() - ($width * 2)),
                (int) round($image->height() - ($width * 2))
            )
            ->resizeCanvas(
                (int) round($width * 2),
                (int) round($width * 2),
                'center',
                true,
                $color
            )
        ;
    }

    /**
     * Run the expand border method.
     *
     * @param Image  $image the source image
     * @param float  $width the border width
     * @param string $color the border color
     *
     * @return Image the manipulated image
     */
    public function runExpand(Image $image, $width, $color)
    {
        return $image->resizeCanvas(
            (int) round($width * 2),
            (int) round($width * 2),
            'center',
            true,
            $color
        );
    }
}
