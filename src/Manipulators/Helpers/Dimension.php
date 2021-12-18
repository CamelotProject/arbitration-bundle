<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators\Helpers;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 */
class Dimension
{
    /** The source image. */
    protected Image $image;
    /** The device pixel ratio. */
    protected int|float $dpr;

    /**
     * Create dimension helper instance.
     *
     * @param Image $image the source image
     * @param float $dpr   the device pixel ratio
     */
    public function __construct(Image $image, int|float $dpr = 1)
    {
        $this->image = $image;
        $this->dpr = $dpr;
    }

    /**
     * Resolve the dimension.
     *
     * @param string $value the dimension value
     *
     * @return null|float the resolved dimension
     */
    public function get(string $value): ?float
    {
        if (is_numeric($value) && $value > 0) {
            return (float) $value * $this->dpr;
        }

        if (preg_match('/^(\d{1,2}(?!\d)|100)(w|h)$/', $value, $matches)) {
            if ($matches[2] === 'h') {
                return (float) $this->image->height() * ((float) $matches[1] / 100);
            }

            return (float) $this->image->width() * ((float) $matches[1] / 100);
        }

        return null;
    }
}
