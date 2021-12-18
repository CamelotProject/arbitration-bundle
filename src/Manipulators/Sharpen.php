<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $sharp
 */
class Sharpen extends BaseManipulator
{
    /**
     * Perform sharpen image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        $sharpen = $this->getSharpen();

        if ($sharpen !== null) {
            $image->sharpen($sharpen);
        }

        return $image;
    }

    /**
     * Resolve sharpen amount.
     *
     * @return null|int the resolved sharpen amount
     */
    public function getSharpen()
    {
        if (!is_numeric($this->sharp)) {
            return;
        }

        if ($this->sharp < 0 || $this->sharp > 100) {
            return;
        }

        return (int) $this->sharp;
    }
}
