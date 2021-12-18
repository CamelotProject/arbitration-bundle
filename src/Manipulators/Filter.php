<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $filt
 */
class Filter extends BaseManipulator
{
    /**
     * Perform filter image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        if ($this->filt === 'greyscale') {
            return $this->runGreyscaleFilter($image);
        }

        if ($this->filt === 'sepia') {
            return $this->runSepiaFilter($image);
        }

        return $image;
    }

    /**
     * Perform greyscale manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function runGreyscaleFilter(Image $image)
    {
        return $image->greyscale();
    }

    /**
     * Perform sepia manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function runSepiaFilter(Image $image)
    {
        $image->greyscale();
        $image->brightness(-10);
        $image->contrast(10);
        $image->colorize(38, 27, 12);
        $image->brightness(-10);
        $image->contrast(10);

        return $image;
    }
}
