<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $sharpen
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
    public function run(Image $image): Image
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
    public function getSharpen(): ?int
    {
        if (!is_numeric($this->sharpen)) {
            return null;
        }

        if ($this->sharpen < 0 || $this->sharpen > 100) {
            return null;
        }

        return (int) $this->sharpen;
    }
}
